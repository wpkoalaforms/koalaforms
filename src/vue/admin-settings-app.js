import { createApp } from 'vue';
import SettingsPage from './components/settings/SettingsPage.vue';

document.addEventListener('DOMContentLoaded', () => {
    const mountPoint = document.getElementById('kf-settings-app');

    if (!mountPoint) {
        return;
    }

    const payload = window.koalaformsSettingsData || {};

    createApp(SettingsPage, payload).mount(mountPoint);
});
