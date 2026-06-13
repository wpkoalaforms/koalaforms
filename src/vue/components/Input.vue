<template>
  <!-- Conditionally render the label if inputLabel is provided -->
  <label v-if="config.attrs.inputLabel" :for="uniqueId">
    {{ config.attrs.inputLabel }}

    <span v-if="config.attrs.required" class="required-field-label">*</span>
  </label>

  <!-- Conditionally render the text field -->
  <div :class="['kf-block-wrap kf-form-field', config.attrs.className]"> 
    
    <input
      v-if="config.attrs.mask"
      :id="uniqueId"
      :type="type"
      :name="config.attrs.name"
      :placeholder="config.attrs.placeholder"
      :readonly="config.attrs.readOnly"
      :required="config.attrs.required"
      :minlength="config.attrs.minLength"
      :maxlength="config.attrs.maxLength"
      :min="minAttr"
      :max="maxAttr"
      :pattern="config.attrs.pattern"
      :class="['kf-form-control', config.attrs.className]"
      v-mask="this.config.attrs.mask"
      :value="modelValue"
      @input="handleUpdate"
    />
    
    <input 
      v-else-if="type=='checkbox'"
      type="checkbox" 
      :checked="modelValue"
      id="uniqueId" 
      name="config.attrs.name" 
      :value="modelValue"
      @input="handleCB" />

    <input
      v-else
      :id="uniqueId"
      :type="type"
      :name="config.attrs.name"
      :placeholder="config.attrs.placeholder"
      :readonly="config.attrs.readOnly"
      :required="config.attrs.required"
      :minlength="config.attrs.minLength"
      :maxlength="config.attrs.maxLength"
      :min="minAttr"
      :max="maxAttr"
      :pattern="config.attrs.pattern"
      :class="['kf-form-control', config.attrs.className]"
      :value="modelValue"
      @input="handleUpdate"
    />
  </div>
</template>

<script src="./js/input.js"></script>