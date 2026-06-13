import { v4 as uuidv4 } from 'uuid';
import { mask } from "vue-the-mask";
import util from './utility';

export default {
    directives: { mask },
    name: 'Input',
    props: {
      // Prop to accept the configuration object
      config: {
        type: Object,
        required: true
      },
      modelValue: [String, Number, Boolean, null],
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
        handleCB(event){
            this.$emit("update:modelValue", event.target.checked); 
            this.$emit("validate", {config: this.config, event, value: event.target.checked, type: this.type});
        }
    },
    computed:{
        type(){
            return util.elementType(this.config.blockName);
        },
        minAttr(){
            if(this.type == 'date') 
                return this.config.attrs?.minDate;
            return this.config.attr?.min;
        },
        maxAttr(){
            if(this.type == 'date') 
                return this.config.attrs?.maxDate;
            return this.config.attr?.max;
        },
        uniqueId() {
            return `${this.config.attrs.name}-${uuidv4()}`;
        }
    },
    mounted(){
        const initialValue = util.initializeModelValue(this.config, this.modelValue);
        if (initialValue) {
          this.$emit('update:modelValue',initialValue);
        }
    }
  };