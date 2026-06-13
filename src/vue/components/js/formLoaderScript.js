import Form from '../Form.vue';
import Element from '../Element.vue';

export default {
    name: 'FormLoader',
    components: {
        Form,
        Element,
    },
    data() {
        return {
            isLoading: true,
            formData: {}, // Global object to store field values
            formConfig: {},
            channel: {name: 'test'}
        };
    },
    mounted() {
        setTimeout(() => {
            this.isLoading = false;
        }, 1000);
    },
};
