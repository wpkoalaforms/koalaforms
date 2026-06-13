import DOMPurify from 'dompurify';
import util from '../../components/js/utility';
import Column from '../../components/Column.vue';
import Columns from '../../components/Columns.vue';
import axios from 'axios';

export default {
    name: 'Address',

    // Referenced components in html
    components: { Column, Columns },

    // Props passed from parent component
    props: {
        config: {
            type: Object,
            required: true,
        },
        formData: {
            type: Object,
            required: true,
        },
        channel: {
            type: Object,
        },
        modelValue:{
            type: Object,
            default: () => ({})
        },
        errors:{
            type: Object,
            default: () => ({})
        }
    },

    // Data to be used in the component
    data() {
        return {
            blockMap: null
        };
    },

    // Template for the component
    methods: {

        // Extract style from the block
        extractStyle(html) {
            const { cls, style } = util.extractAttributes(DOMPurify.sanitize(html));
            return style;
        },

        // Extract class from the block
        extractClass(html) {
            const { cls } = util.extractAttributes(DOMPurify.sanitize(html));
            if (util.elementType(this.config.blockName) === 'column') {
                return `kf-column-wrapper ${cls}`;
            }
            return cls;
        },

        // Emit validate event and update the form data
        async validate(data) {
            const { config, event, value, type } = data;
            if (this.config.attrs?.name) {
                const address = { ...this.modelValue, [config.attrs.name]: value };
                this.$emit("validate", { config: this.config, event, value: address, type: 'address' });

                // Check if any other field need to be populated
                if (config.attrs.subtype == 'country' && this.blockMap.state && value) {
                    const states = await this.fetchStates(value);

                    if (states.length ==0){
                        this.channel[this.blockMap.state.attrs.name] = { text: true, options: []}
                    }

                    if (states.length > 1){
                        this.channel[this.blockMap.state.attrs.name] = { options: states };
                    }
                }

                // Check if country is hidden but state is not hidden then change state to a text field instead
                this.$emit('update:modelValue', address);
            }
        },

        // Fetch countries
        async fetchCountries() {
            const data = { action: 'koalaforms_get_countries', nonce: koalaformsAjax.load_form_nonce}
            const [response, error] = await util.handleAsync(axios.post(koalaformsAjax.ajax_url, util.prepareForm(data)));
            if (response.success) {
                const { countries } = response.data;
                return countries;
            }
            return [];
        },

        // Fetch States based on country
        async fetchStates(country) {
            const data = { action: 'koalaforms_get_states', country , nonce: koalaformsAjax.load_form_nonce};
            const [response, error] = await util.handleAsync(axios.post(koalaformsAjax.ajax_url, util.prepareForm(data)));
            if (response.success) {
                const { states } = response.data;
                return states;
            }
            return [];
        }
    },

    // Create a map of all blocks in the address block
    async created() {
        // Only run for top-level parent (e.g., address)
        if (util.elementType(this.config.blockName) === 'address') {
            const hiddenAddressFields = this.config.attrs.hiddenAddressFields || [];
            this.blockMap = util.flattenInnerBlocks(this.config)
                .reduce((acc, block) => {
                    acc[block.attrs.subtype] = block;
                    if (hiddenAddressFields.includes(block.attrs.subtype)){
                        block.attrs.hidden = true;
                    }
                    return acc;
                }, {});
            if (this.blockMap.country) {
                this.channel[this.blockMap.country.attrs.name] = { options: await this.fetchCountries() };
            }

            // check if country is hidden but state is visible
            if (hiddenAddressFields.includes('country') && !hiddenAddressFields.includes('state')){
                this.channel[this.blockMap.state.attrs.name] = { type: 'text' };
            }
        }
        
        
    },
    computed:{
        blockData(){
            return {...this.modelValue, errors: this.errors};
        }
    }
};