import { v4 as uuidv4 } from 'uuid';
import util from './utility';

export default {
    name: 'ChoiceInput',
    props: {
      // Prop to accept the configuration object
      config: {
        type: Object,
        required: true
      },
      modelValue: [String, Number, null],
      formData: {
        type: Object
      },
      channel: {
        type: Object
      }
    },
    data(){
        return {
            error: "",
            text: false
        }
    },
    methods: {
        handleUpdate(event) {
            this.$emit('update:modelValue', event.target.value);
            this.$emit("validate", {config: this.config, event, value: event.target.value, type: this.type});
        }
    },
    watch:{
        channel: {
             handler(newChannelData) {
                if(newChannelData?.[this.config.attrs.name]?.options.length){
                    this.text = false;
                    this.config.attrs.options = newChannelData[this.config.attrs.name].options;
                    delete newChannelData[this.config.attrs.name];
                }
                if(newChannelData?.[this.config.attrs.name]?.text){
                    this.text = true;
                    delete newChannelData[this.config.attrs.name];
                }
              }, 
            deep: true
        }
    },
    computed:{
        type(){
            return util.elementType(this.config.blockName);
        },
        isSelectInput() {
            return this.type === 'select';
        },
        isCheckboxInput() {
            return this.type === 'checkbox';
        },
        uniqueId() {
            return `${this.config.attrs.name}-${uuidv4()}`;
        },
        isRadioInput() {
            return this.type === 'radio';
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