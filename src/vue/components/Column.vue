<template>
    <template v-for="(block, index) in config.innerBlocks" :key="index">
        <Columns v-if="isGroupElement(block.blockName)" :config="block" @validate="validate" :channel="channel"/>
        <Element v-else :config="block" v-model="formData[block.attrs.name]" :form-data="formData" @validate="validate" :channel="channel"/>
    </template>
</template>

<script>
import Element from '../components/Element.vue';
import util from './js/utility';
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
              
const components = { Element }

export default {
  name: 'Column',
  components,
  props,
  data() {},
  methods: {
    isGroupElement(blockName){
      return util.isGroupElement(blockName);
    },
    validate(data){
      this.$emit("validate", data);
    }
  },
  async mounted() {

  },
};

</script>
