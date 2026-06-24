import { createApp } from 'vue';
import AnalyticsPage from './components/analytics/AnalyticsPage.vue';

document.addEventListener('DOMContentLoaded', () => {
    const mountPoint = document.getElementById('kf-analytics-app');
    if (!mountPoint) return;
    const payload = window.koalaformsAnalyticsData || {};
    createApp(AnalyticsPage, { data: payload }).mount(mountPoint);
});
