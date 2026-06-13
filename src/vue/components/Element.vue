<template>
  <div :class="['kf-element-wrapper',wrapperClass]">
    <!-- Dynamically load the Input component based on the form definition -->
     <Input v-if="isInput" :config="config" v-model="formData[config.attrs.name]" :form-data="formData" @validate="validate" :channel="channel"/>
     <ChoiceInput v-if="isChoiceInput" :config="config"  v-model="formData[config.attrs.name]" :form-data="formData" @validate="validate" :channel="channel"/>
     <MultiChoiceInput v-if="isMultiChoiceInput" :config="config"  v-model="formData[config.attrs.name]" :form-data="formData" @validate="validate" :channel="channel"/>
     <Html v-if="isHtml" :config="config"  v-model="formData[config.attrs.name]" :form-data="formData" @validate="validate" :channel="channel"/>
     <Textarea v-if="isTextarea" :config="config"  v-model="formData[config.attrs.name]" :form-data="formData" @validate="validate" :channel="channel"/>

     <div v-if="fieldError" class="kf-field-error">
        {{fieldError}}
     </div>
  </div>
</template>

<script>
import Input from "./Input.vue";
import ChoiceInput from "./ChoiceInput.vue";
import MultiChoiceInput from "./MultiChoiceInput.vue";
import Html from "./Html.vue";
import Textarea from './Textarea.vue';
import util from './js/utility';

export default {
  name: "Element",
  components: {
    Input,
    ChoiceInput,
    util,
    MultiChoiceInput,
    Html,
    Textarea
  },
  props: {
    // Prop to accept the configuration object, which defines the properties and behavior of the element
    config: {
      type: Object,
      required: true,
    },
    formData: {
      type: Object,
      required: true,
    },
    channel:{
        type: Object
    }
  },
  computed: {
    isInput(){
      return util.isInput(this.config.blockName);
    },
    isChoiceInput(){
      return util.isChoiceInput(this.config.blockName);
    },
    isMultiChoiceInput(){
      return util.isMultiChoiceInput(this.config.blockName);
    },
    isHtml(){
      return util.isHtml(this.config.blockName);
    },
    isTextarea(){
      return util.isTextarea(this.config.blockName);
    },
    wrapperClass(){
      let className = `kf-${util.elementType(this.config.blockName)}`

      if(this.config?.attrs?.hidden){
        className += ' kf-hidden';
      }
      return className;
    },
    fieldError(){
      return this.formData?.errors?.[`${this.config.attrs.name}`];
    }
  },
  methods:{
    validate(data){
      this.$emit('validate', data);
    }
  },
  mounted(){
    if(this.formData.type == 'registration'){

      if(this.formData.user_logged_in && this.formData.primary_email_field == this.config.attrs.name){
        this.config.attrs.readOnly = true;
      }

      if(this.formData.user_logged_in && this.formData.username_field == this.config.attrs.name){
        this.config.attrs.hidden = true;
      }
    }
  }
};
</script>