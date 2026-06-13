import { v4 as uuidv4 } from 'uuid';
import util from './utility';

export default {
  name: 'Html',
  props: {
    config: {
      type: Object,
      required: true,
    },
    modelValue: {
      type: [Boolean],
      default: this?.config?.attrs?.defaultCBValue,
    },
    formData: {
      type: Object
    },
    channel:{
      type: Object
    }
  },
  data() {
    return {
      error: '',
    };
  },
  methods: {
    handleUpdate(event) {
      this.$emit("update:modelValue", event.target.checked); 
      this.$emit("validate", {config: this.config, event, value: event.target.checked, type: this.type});
    }
  },
  computed: {
    type() {
      return util.elementType(this.config.blockName);
    },
    wrapperClass() {
      return `form-group kf-${this.type}`;
    },
    uniqueId() {
      return `${this.config.attrs.name}-${uuidv4()}`;
    },
    isParagraph(){
      return this.type == 'paragraph';
    },
    isDisclosure(){
      return this.type == 'disclosure';
    }
  },
  mounted(){
    const initialValue = util.initializeModelValue(this.config, this.modelValue);
    if (initialValue) {
      this.$emit('update:modelValue',initialValue);
    }
  }
};