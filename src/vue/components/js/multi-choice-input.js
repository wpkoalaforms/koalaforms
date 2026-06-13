import { v4 as uuidv4 } from 'uuid';
import util from './utility';

export default {
    name: 'MultiChoiceInput',
    props: {
      // Prop to accept the configuration object
      config: {
        type: Object,
        required: true
      },
      modelValue: {
        type: Array,
        default: []
      },
      formData: {
        type: Object
      },
      channel:{
        type: Object
      }
    },
    data(){
        return {
            error: ""
        }
    },
    methods: {
        handleUpdate(value, checked) {
          let newValue = [...this.modelValue];
          if (checked) {
            newValue.push(value); // Add checked value
          } else {
            newValue = newValue.filter(item => item !== value); // Remove unchecked value
          }
          this.$emit("update:modelValue", newValue); // Emit updated array
          this.$emit("validate", {config: this.config, event, value: newValue, type: this.type});
        }
    },
    computed:{
        type(){
            return util.elementType(this.config.blockName);
        },
        uniqueId() {
            return `${this.config.attrs.name}-${uuidv4()}`;
        },
        hasCardOptions() {
            return this.config.attrs.options?.some((opt) => opt.description);
        },
    },
    mounted(){
      const initialValue = util.initializeModelValue(this.config, this.modelValue, true);
      if (initialValue) {
        this.$emit('update:modelValue',initialValue);
      }
    }
  };