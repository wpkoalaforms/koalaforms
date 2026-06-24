import Form from '../Form.vue';
import Element from '../Element.vue';
import axios from 'axios';
import util from './utility';
import Columns from '../Columns.vue'
import Address from '../Address.vue'
import FormNavigation from '../../components/FormNavigation.vue';
import Captcha from '../Captcha.vue';

const props = {
  formId: {
    type: String,
    required: true,
  }
}

const components = { Form, Element, Columns, FormNavigation , Address, Captcha}

export default {
  name: 'FormLoader',
  components,
  props,
  data() {
    return {
      isLoading: true,
      successMessage: '',
      formData: {}, // Object to store field values, scoped to this instance
      channel: {name: 'tt'},
      formConfig: null, // Stores configuration for current step
      errorMessage: '',
      currentIndex: 0,
      transitionDirection: 'forward',
      formSchema: [], // Stores all the steps and configuration,
      submissionNonce: null,
      captchaDetails: null,
      isSubmitting: false,
      showErrorBanner: false,
      errorBannerTimer: null,
    };
  },
  methods: {
    onCaptchaVerify(token){
      this.formData.recaptcha = token;
      if(this.formData?.errors?.captcha){
        delete this.formData.errors.captcha;
      }
    },
    onCaptchaExpiry(){
      this.formData.recaptcha = null;
      if(this.formData?.errors?.captcha){
        delete this.formData.errors.captcha;
      }
    },
    // Loads form schema from server that is used for rendering.
    async loadFormSchema() {
      const formData = { action: 'koalaforms_load_form', nonce: koalaformsAjax.load_form_nonce, form_id: this.formId }
      const [response, error] = await util.handleAsync(axios.post(koalaformsAjax.ajax_url, util.prepareForm(formData)));
      if (response.success) {
        const { elements, submissionNonce, captcha_details,
                username_field, primary_email_field, user_email,
                user_logged_in, type } = response.data;
        // Filtering all the steps
        const steps           = elements.filter((item) => util.elementType(item.blockName) == 'step');
        this.formConfig       = steps[this.currentIndex];
        this.formSchema       = steps;
        this.submissionNonce  = submissionNonce;
        this.captchaDetails   = captcha_details;
        this.formData.type    = type;
        
        if(user_logged_in){
            if(primary_email_field){
              this.formData[primary_email_field] = user_email;
              this.formData['primary_email_field'] = primary_email_field;
            }
        }

        if(username_field){
          this.formData['username_field'] = username_field;
        }
        
        this.formData['user_logged_in'] = user_logged_in;
      }
      this.errorMessage = error ?? response?.data?.error;
    },

    // if element has inner elements to display
    isGroupElement(blockName) {
      return util.isGroupElement(blockName);
    },
    isAddressElement(blockName) {
      return util.isAddressElement(blockName);
    },

    handleModelUpdate(newValue) {
      this.formData = { ...this.formData, ...newValue, errors: {} };
    },

    flashErrorBanner() {
      clearTimeout(this.errorBannerTimer);
      this.showErrorBanner = true;
      this.errorBannerTimer = setTimeout(() => { this.showErrorBanner = false; }, 4000);
    },

    // Moving to next step
    async handleNextStep() {
      if (this.isSubmitting) return;
      this.isSubmitting = true;
      try {
      this.formData.last_step = this.currentIndex == (this.formSchema.length - 1);
      const [response, errors]  = await this.submitForm();
      if (response.success) {
        // Check for any errors returned in the response
        const { errors = {}, form_errors = [] } = response.data;

        if (Object.entries(errors).length){ // Since there are errors, Do not move forward.
          this.formData.errors = errors;
          this.flashErrorBanner();
          return;
        }

        if (form_errors.length){ // Since there are errors, Do not move forward.
          this.formData.form_errors = form_errors;
          this.flashErrorBanner();
          return;
        }

        this.transitionDirection = 'forward';
        this.formConfig = this.formSchema[++this.currentIndex];
        const {redirection, success_message} = response.data;

        if(success_message){
          this.successMessage = success_message;
        }

        if(redirection && redirection.trim()){
          setTimeout(() => {
            window.location.href = redirection;
          }, 1000);
        }
        setTimeout(() => {
          if(this.showCaptcha && this.captchaDetails?.type == 'invisible'){
            this.$refs.captcha.triggerExecute();
          }
        }, 1000);

      }
      } finally {
        this.isSubmitting = false;
      }
    },

    validate({config, event, value, type}){
      const { required, pattern, patternError, inputLabel,name, requiredError } = config.attrs;
      //this.formData = { ...this.formData, [name]: value };
      let error = { [name]: `${requiredError}` }
      let isValid = true; 
        // Checking if field is required
        if (required){
            if (type == 'number'){
                if (isNaN(Number(value)))
                  isValid = false;
            }
            else if (type=='multiselect'){
                if(Array.isArray(value) && value.length == 0){
                  isValid = false;
                }
            }
            else if (!value || !value.trim()){
                isValid = false;
            }
        }

        if(isValid){
            delete this.formData?.errors?.[name];
            return;
        }
        
        this.formData.errors = {...this.formData.errors, ...error} ;
    },

    // Moving to previous step
    handlePreviousStep() {
      this.transitionDirection = 'backward';
      this.formConfig = this.formSchema[--this.currentIndex]
    },

    goToStep(index) {
      if (index === this.currentIndex || !this.formSchema[index] || index > this.currentIndex) {
        return;
      }

      this.transitionDirection = 'backward';
      this.currentIndex = index;
      this.formConfig = this.formSchema[index];
    },

    stepTitle(step, index) {
      const title = step?.attrs?.displayLabel || step?.attrs?.inputLabel || ``;
      return title
        .toString()
        .replace(/_/g, ' ')
        .replace(/\b\w/g, (char) => char.toUpperCase());
    },

    // Submits the form asynchornously 
    async submitForm() {
      this.form_errors = [];
      const currentStepName = this.formSchema[this.currentIndex]?.attrs?.name;
      const formData =  {  ...this.formData, action: 'koalaforms_submit_form', nonce: this.submissionNonce, 
                          form_id: this.formId, current_step: currentStepName
                        }
      return await util.handleAsync(axios.post(koalaformsAjax.ajax_url, util.prepareForm(formData)));
    }

  },
  async mounted() {
    await this.loadFormSchema();
    this.isLoading = false;
  },
  beforeUnmount() {
    clearTimeout(this.errorBannerTimer);
  },
  computed: {
    showNav() {
      return this.formConfig.attrs.nextBtnLabel && this.formConfig.attrs.prevBtnLabel;
    },
    hasPrevious() {
      return this.currentIndex > 0;
    },
    hasNext() {
      return this.currentIndex < (this.formSchema.length - 1);
    },
    formError(){
      return this.formData?.errors?.form || this.formData?.errors?.captcha;
    },
    errorCount(){
      return Object.keys(this.formData?.errors || {}).length;
    },
    showCaptcha(){
      return !this.hasNext && this.captchaDetails?.vendor != 'none';
    },
    heading(){
      return this.stepTitle(this.formConfig, this.currentIndex);
    },
    totalSteps() {
      return this.formSchema.length || 1;
    },
    isLastStep() {
      return this.currentIndex === (this.totalSteps - 1);
    },
    stepTransitionName() {
      return this.transitionDirection === 'forward' ? 'kf-step-forward' : 'kf-step-backward';
    }
  }
};
