import { createApp } from 'vue';
import DashboardPage from './components/dashboard/DashboardPage.vue';

document.addEventListener('DOMContentLoaded', () => {
    const mountPoint = document.getElementById('kf-dashboard-app');

    if (!mountPoint) {
        return;
    }

    const payload = window.kfDashboardPageData || {};

    createApp(DashboardPage, { data: payload }).mount(mountPoint);
});
