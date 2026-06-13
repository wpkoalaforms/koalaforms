<template>

  <!-- Conditionally render the label if inputLabel is provided -->
  <label v-if="config.attrs.inputLabel" :for="uniqueId">
    {{ config.attrs.inputLabel }}
    <span v-if="config.attrs.required" class="required-field-label">*</span>
  </label>
  
  <!-- Card layout when any option has a description -->
  <template v-if="hasCardOptions">
    <label
      v-for="(option, index) in config.attrs.options"
      :key="index"
      :class="['kf-radio-card', { 'kf-radio-card--selected': modelValue.includes(option.value) }]"
      :for="`checkbox-${config.attrs.name}-${index}`"
    >
      <input
        type="checkbox"
        :id="`checkbox-${config.attrs.name}-${index}`"
        :name="config.attrs.name"
        :readonly="config.attrs.readOnly"
        :value="option.value"
        :checked="modelValue.includes(option.value)"
        @change="handleUpdate(option.value, $event.target.checked)"
      />
      <div class="kf-radio-card__content">
        <strong class="kf-radio-card__label">{{ option.label }}</strong>
        <span v-if="option.description" class="kf-radio-card__description">{{ option.description }}</span>
      </div>
    </label>
  </template>

  <!-- Plain layout when no descriptions -->
  <template v-else>
    <div
      v-for="(option, index) in config.attrs.options"
      :key="index"
      :class="['field-wrapper', config.attrs.className]"
    >
      <input
        type="checkbox"
        :id="`checkbox-${config.attrs.name}-${index}`"
        :name="config.attrs.name"
        :readonly="config.attrs.readOnly"
        :value="option.value"
        :checked="modelValue.includes(option.value)"
        @change="handleUpdate(option.value, $event.target.checked)"
      />
      <label :for="`checkbox-${config.attrs.name}-${index}`">{{ option.label }}</label>
    </div>
  </template>

  <div v-if="error" class="error-message">{{ error }}</div>
</template>

<script src="./js/multi-choice-input.js"></script>

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

.kf-radio-card input[type="checkbox"] {
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