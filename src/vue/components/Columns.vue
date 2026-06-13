<template>
  <div class="kf-form-row">
    <div v-for="(block, index) in config.innerBlocks" :key="index" 
        :style="extractStyle(block.innerHTML)" :class="extractClass(block.innerHTML)">
        <Column :config="block" :form-data="formData" @validate="validate" :channel="channel"/>
    </div>
  </div>
</template>

<script>
//import Element from '../Element.vue';
import DOMPurify from 'dompurify';
import util from './js/utility';
import Column from '../components/Column.vue';

const props = {
                config: {
                  type: Object,
                  required: true,
                },
                formData: {
                  type: Object,
                  required: true,
                },
                channel: {
                  type: Object
                }
              }
              
const components = { Column }

export default {
  name: 'Columns',
  components,
  props,
  data() {},
  methods: {
    extractStyle(html){
      const {cls, style} = util.extractAttributes(DOMPurify.sanitize(html));
      return style;
    },
    extractClass(html){
      const {cls, style} = util.extractAttributes(DOMPurify.sanitize(html));
      if(util.elementType(this.config.blockName) == 'column'){
        cls = `kf-column-wrapper ${cls}`;
      }
      return cls;
    },
    validate(data){
      this.$emit("validate", data);
    }
  },
  async mounted() {},
};

</script>
