<template>
  <div class="kf-step-buttons">
    <button
      v-if="showPrevious"
      type="button"
      class="kf-step-action-button kf-step-action-button-secondary"
      :style="previousButtonStyle"
      @click="goPrevious"
    >
      {{ config.prevBtnLabel }}
    </button>

    <button
      type="button"
      class="kf-step-action-button kf-step-action-button-primary"
      :style="nextButtonStyle"
      :disabled="isSubmitting"
      @click="goNext"
    >
      <span v-if="isSubmitting" class="kf-btn-spinner"></span>
      <span v-else>{{ showNext ? config.nextBtnLabel : 'Submit' }}</span>
    </button>
  </div>
</template>
<script>
export default {
  name: "FormNavigation",
  props: {
    config: {
      type: Object,
      default: null,
    },
    showPrevious: {
      type: Boolean,
      default: false,
    },
    showNext: {
      type: Boolean,
      default: false,
    },
    isSubmitting: {
      type: Boolean,
      default: false,
    },
  },
  methods: {
    goNext() {
      this.$emit("navigate-next");
    },
    goPrevious() {
      this.$emit("navigate-previous");
    },
  },
  computed: {
    previousButtonStyle() {
      const width = Math.max(Number(this.config?.previousWidth || 3), 1);
      return {
        flex: `0 0 ${Math.max((width / 12) * 100, 25)}%`,
        minWidth: '132px',
      };
    },
    nextButtonStyle() {
      const width = Math.max(Number(this.config?.nextWidth || 3), 1);
      return {
        flex: `0 0 ${Math.max((width / 12) * 100, 25)}%`,
        minWidth: '148px',
      };
    },
  },
};
</script>
<style scoped>
.kf-btn-spinner {
  display: inline-block;
  width: 16px;
  height: 16px;
  border: 2px solid currentColor;
  border-top-color: transparent;
  border-radius: 50%;
  animation: kf-spin 0.7s linear infinite;
}
@keyframes kf-spin {
  to { transform: rotate(360deg); }
}
</style>
