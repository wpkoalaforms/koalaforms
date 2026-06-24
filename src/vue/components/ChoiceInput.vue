<template>
  <!-- Conditionally render the label if inputLabel is provided -->
  <label v-if="fieldLabel" :for="uniqueId">
    {{ fieldLabel }}
    <span v-if="config.attrs.required" class="required-field-label">*</span>
  </label>

  <!-- Render the radio buttons based on the options provided in the config -->
  <div
    v-if="isRadioInput"
    :class="['kf-block-wrap kf-form-field', config.attrs.className, `display-mode-${config.attrs.displayMode || 'vertical'}`]"
  >
    <!-- Card layout when any option has a description -->
    <template v-if="hasCardOptions">
      <label
        v-for="(option, index) in config.attrs.options"
        :key="index"
        :class="['kf-radio-card', { 'kf-radio-card--selected': modelValue === option.value || (!modelValue && option.default) }]"
        :for="`radio-${config.attrs.name}-${index}`"
      >
        <input
          type="radio"
          :id="`radio-${config.attrs.name}-${index}`"
          :name="config.attrs.name"
          :value="option.value"
          :checked="modelValue === option.value || (!modelValue && option.default)"
          :readonly="config.attrs.readOnly"
          :required="config.attrs.required"
          @input="handleUpdate"
        />
        <div class="kf-radio-card__content">
          <strong class="kf-radio-card__label">{{ option.label }}</strong>
          <span v-if="option.description" class="kf-radio-card__description">{{ option.description }}</span>
        </div>
      </label>
    </template>

    <!-- Plain layout when no descriptions -->
    <template v-else>
      <div v-for="(option, index) in config.attrs.options" :key="index">
        <input
          type="radio"
          :id="`radio-${config.attrs.name}-${index}`"
          :name="config.attrs.name"
          :value="option.value"
          :checked="modelValue === option.value || (!modelValue && option.default)"
          :readonly="config.attrs.readOnly"
          :required="config.attrs.required"
          @input="handleUpdate"
        />
        <label :for="`radio-${config.attrs.name}-${index}`">{{ option.label }}</label>
      </div>
    </template>
  </div>

  <!-- Render the Dropdown based on the options provided in the config -->
  <div
    v-if="isSelectInput"
    :class="['kf-block-wrap kf-form-field', config.attrs.className]"
  >
    <input
      v-if="text"
      :id="uniqueId"
      :type="text"
      :name="config.attrs.name"
      :readonly="config.attrs.readOnly"
      :required="config.attrs.required"
      :class="['kf-form-control', config.attrs.className]"
      :value="modelValue"
      @input="handleUpdate"
    />

    <select
      v-else
      :value="modelValue"
      :required="config.attrs.required"
      :disabled="config.attrs.readOnly"
      type="select"
      :class="['kf-form-control kf-select-field', config.attrs.className]"
      :id="`select-${uniqueId}`"
      :name="config.attrs.name"
      @change="handleUpdate"
    >
      <!-- Default option -->
      <!-- <option disabled value="">Please select a value</option> -->
      <!-- Loop through options and create dropdown options -->
      <option
        v-for="option in config.attrs.options"
        :key="option.value"
        :value="option.value"
      >
        {{ option.label }}
      </option>
    </select>

  </div>

  <div v-if="error" class="error-message">{{ error }}</div>
</template>

<script src="./js/choice-input.js"></script>

<style scoped>
.kf-radio-card {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  padding: 14px 16px;
  border: 1px solid #ddd;
  border-radius: 8px;
  cursor: pointer;
  margin-bottom: 8px;
  background: #fff;
  transition: background 0.15s, border-color 0.15s;
}

.kf-radio-card--selected {
  background: #e0f2ef;
  border-color: #2d7a68;
}

.kf-radio-card input[type="radio"] {
  margin-top: 2px;
  flex-shrink: 0;
  accent-color: #2d7a68;
  width: 18px;
  height: 18px;
  cursor: pointer;
}

.kf-radio-card__content {
  display: flex;
  flex-direction: column;
  gap: 3px;
}

.kf-radio-card__label {
  font-weight: 600;
  color: #1a1a1a;
  font-size: 1em;
}

.kf-radio-card__description {
  font-size: 0.9em;
  color: #555;
}
</style>