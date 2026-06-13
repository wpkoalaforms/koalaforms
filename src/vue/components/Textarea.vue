<template>
  <!-- Conditionally render the label if inputLabel is provided -->
  <label v-if="config.attrs.inputLabel" :for="uniqueId">
    {{ config.attrs.inputLabel }}

    <span v-if="config.attrs.required" class="required-field-label">*</span>
  </label>

  <!-- Conditionally render the text field -->
  <div :class="['kf-block-wrap kf-form-field', config.attrs.className]">
    <textarea
      :id="uniqueId"
      :name="config.attrs.name"
      :placeholder="config.attrs.placeholder"
      :readonly="config.attrs.readOnly"
      :required="config.attrs.required"
      :rows="config.attrs.rows"
      :class="['kf-form-control kf-input-textarea', config.attrs.className]"
      :value="modelValue"
      @input="handleUpdate" />
    <div v-if="error" class="error-message">{{ error }}</div>
  </div>
</template>

<script>

import { v4 as uuidv4 } from 'uuid';

export default {
    name: 'Textarea',
    props: {
      // Prop to accept the configuration object
      config: {
        type: Object,
        required: true
      },
      modelValue: [String, null],
      formData: {
        type: Object
      },
      channel:{
        type: Object
      }
    },
    data(){
        return {
            error: "",
        }
    },
    methods: {
        handleUpdate(event) {
            this.$emit('update:modelValue', event.target.value);
            this.$emit("validate", {config: this.config, event, value: event.target.value, type: this.type});
        },
    },
    computed:{
        uniqueId() {
            return `${this.config.attrs.name}-${uuidv4()}`;
        }
    }
  };

</script>