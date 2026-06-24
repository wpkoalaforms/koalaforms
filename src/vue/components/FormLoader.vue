<template>
  <div class="kf-form-container">
    
    <!-- Show a loading indicator while the form is being fetched -->
    <div v-if="isLoading" class="loader">
      <p>Loading...</p>
      <!-- You can add a spinner or some other loading indicator here -->
    </div>

     <!-- Form Submission Success Message-->
    <div v-if="successMessage" class="kf-success-message" v-html="successMessage">
    </div>

    <div v-if="formConfig">
      <div v-if="totalSteps > 1" class="kf-step-flow" aria-label="Step progress">
        <button
          v-for="(step, index) in formSchema"
          :key="step?.attrs?.name || index"
          type="button"
          class="kf-step-flow-item"
          :class="{
            'kf-step-flow-item-active': index === currentIndex,
            'kf-step-flow-item-complete': index < currentIndex,
          }"
          :aria-current="index === currentIndex ? 'step' : null"
          @click="goToStep(index)"
        >
          <span class="kf-step-flow-index">{{ index + 1 }}</span>
          <span class="kf-step-flow-label">{{ stepTitle(step, index) }}</span>
        </button>
      </div>

      <div class="kf-step-card-header">
        <div class="kf-step-card-meta">
          <p class="kf-step-card-kicker">Step {{ currentIndex + 1 }} of {{ totalSteps }}</p>
          <h2 class="kf-step-card-title" v-if="heading">{{ heading }}</h2>
        </div>
      </div>
  
      <!-- 
        This is the form container.
        The purpose of this component is to load the form definition dynamically 
        from the database. Based on the form schema, it loads elements in a loop 
        and passes the configuration for each element to render the form.
      -->
      <Transition :name="stepTransitionName" mode="out-in">
      <Form
        :key="currentIndex"
        action="POST"
        class="kf-front-form-wrapper kf-step-surface"
        autocomplete="off"
      >
        
        <!-- 
          Iterate over the innerBlocks of the formConfig and render a corresponding 
          Element component for each block. 
          Each block's configuration is passed to the Element component dynamically.
        -->

        

        <template v-for="(block, index) in formConfig.innerBlocks" :key="index">
          <Columns v-if="isGroupElement(block.blockName)" :config="block" :form-data="formData" @validate="validate" :channel="channel"/>
          <Address v-if="isAddressElement(block.blockName)" 
                :config="block" :form-data="formData"  @validate="validate" :channel="channel" 
                v-model="formData[block.attrs.name]" :errors="formData?.['errors']?.[block.attrs.name]" />
          <Element v-else :config="block" :form-data="formData" @validate="validate" :channel="channel"/>
        </template>

        <Captcha v-if="showCaptcha" :config="captchaDetails" @verified="onCaptchaVerify" ref="captcha" @expired="onCaptchaExpiry"/>

        <Transition name="kf-fade">
          <div v-if="showErrorBanner && errorCount" class="kf-field-error kf-error-banner" role="alert">
            {{ errorCount === 1 ? 'There is 1 field that needs attention above.' : `There are ${errorCount} fields that need attention above.` }}
          </div>
        </Transition>

        <div v-if="formError" class="kf-field-error">
          {{formError}}
        </div>
        <FormNavigation :config="formConfig.attrs"
                        :show-previous="hasPrevious" :show-next="hasNext"
                        :is-submitting="isSubmitting"
                        @navigate-next="handleNextStep"
                        @navigate-previous="handlePreviousStep"></FormNavigation>

        
      </Form>
      </Transition>
    </div>

    <!-- Shows any errors during form load-->
    <div v-if="errorMessage" class="kf-error">
      {{errorMessage}}
    </div>

    <div v-if="formData.form_errors && formData.form_errors.length">
        <ul class="kf-error">
          <li v-for="(error, index) in formData.form_errors" :key="index" v-html="error"></li>
        </ul>
    </div>
  </div>
</template>

<script src="./js/form-loader.js"></script>
