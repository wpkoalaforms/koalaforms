<template>
    <div v-if="config">
        <vue-recaptcha v-if="isInvisible" size="invisible" ref="recaptcha" :sitekey="config.key" @verify="onVerify" @expired="onExpired"></vue-recaptcha>
        <vue-recaptcha v-else ref="recaptcha"  :sitekey="config.key" @verify="onVerify" @expired="onExpired"></vue-recaptcha>
    </div>
</template>
<script>
import { VueRecaptcha } from 'vue-recaptcha';

export default {
  name: "Captcha",
  components: {VueRecaptcha},
  props: {
    // Prop to accept the configuration object, which defines the properties and behavior of the element
    config: {
      type: Object,
      required: true,
    }
  },
  computed: {
    isInvisible(){
        return this.config?.type == 'invisible'
    }
  },
  methods:{
    // Recaptcha verification callback
    onVerify(response) {
      this.$emit('verified', response);
    },
    // Recaptcha verification callback
    onExpired(response) {
      this.$emit('expired');
    },
    // Only for invisible
    triggerExecute(){
        this.$refs.recaptcha.execute();
    },
    // Recaptcha expired callback
    onRecaptchaExpired() {
      this.formData.recaptcha = '';
    },
  }
};
</script>
