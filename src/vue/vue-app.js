import { createApp } from 'vue';
import FormLoader from './components/FormLoader.vue';


// Mount the Vue app to the element with id "vue-app"

document.addEventListener("DOMContentLoaded", function () {
    const appElement = document.querySelector('.kf-form-app');
    if (appElement) {
        const formId = appElement.dataset.formId; // Access `data-form-id`
        if(!formId) return;
        createApp(FormLoader, { formId }).mount(appElement);
    }
});

