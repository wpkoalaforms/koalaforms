<template>
    <div class="kf-analytics-page">
        <h1 class="kf-analytics-page__title">Analytics</h1>

        <AnalyticsControls
            :forms="data.forms || []"
            :selectedFormId="selectedFormId"
            :isSaving="isSaving"
            @update:selectedFormId="onFormChange"
            @open-add-modal="showAddModal = true"
        />

        <div v-if="!selectedFormId" class="kf-analytics-page__empty">
            Select a form above to view its analytics.
        </div>

        <template v-else>
            <div v-if="charts.length === 0" class="kf-analytics-page__empty">
                No charts configured. Click "+ Add Chart" to get started.
            </div>
            <div v-else class="kf-chart-grid">
                <ChartCard
                    v-for="chart in charts"
                    :key="chart.id"
                    :config="chart"
                    :chartData="results[chart.id] ?? null"
                    :isLoading="isLoading"
                    @remove="removeChart(chart.id)"
                    @update-days="updateChartDays(chart.id, $event)"
                />
            </div>
        </template>

        <AddChartModal
            v-if="showAddModal"
            :fields="allFields"
            @add="addChart"
            @close="showAddModal = false"
        />
    </div>
</template>

<script>
import AnalyticsControls from './AnalyticsControls.vue';
import AddChartModal     from './AddChartModal.vue';
import ChartCard         from './ChartCard.vue';

export default {
    name: 'AnalyticsPage',
    components: { AnalyticsControls, AddChartModal, ChartCard },
    props: {
        data: { type: Object, default: () => ({}) },
    },
    data() {
        return {
            selectedFormId: null,
            charts:         [],
            results:        {},
            isLoading:      false,
            isSaving:       false,
            showAddModal:   false,
            saveTimer:      null,
        };
    },
    mounted() {
        const first = this.data.forms?.[0];
        if (first) this.onFormChange(first.id);
    },
    computed: {
        selectedForm() {
            if (!this.selectedFormId || !this.data.forms) return null;
            return this.data.forms.find(f => f.id === this.selectedFormId) || null;
        },
        allFields() {
            return this.selectedForm?.fields || [];
        },
    },
    methods: {
        onFormChange(formId) {
            this.selectedFormId = formId;
            const form = this.data.forms?.find(f => f.id === formId);
            this.charts  = form?.savedCharts ? JSON.parse(JSON.stringify(form.savedCharts)) : [];
            this.results = {};
            if (this.charts.length > 0) {
                this.fetchAllChartData();
            }
        },
        async fetchAllChartData() {
            if (!this.selectedFormId || this.charts.length === 0) return;
            this.isLoading = true;
            try {
                const body = new URLSearchParams({
                    action:  'koalaforms_get_analytics_data',
                    nonce:   this.data.nonce,
                    form_id: this.selectedFormId,
                    charts:  JSON.stringify(this.charts),
                });
                const res  = await fetch(this.data.ajaxUrl, { method: 'POST', body });
                const json = await res.json();
                if (json.success) {
                    this.results = json.data;
                }
            } catch (e) {
                console.error('KoalaForms analytics fetch error', e);
            } finally {
                this.isLoading = false;
            }
        },
        async saveConfig() {
            if (!this.selectedFormId) return;
            this.isSaving = true;
            try {
                const body = new URLSearchParams({
                    action:  'koalaforms_save_analytics_config',
                    nonce:   this.data.nonce,
                    form_id: this.selectedFormId,
                    charts:  JSON.stringify(this.charts),
                });
                await fetch(this.data.ajaxUrl, { method: 'POST', body });
            } catch (e) {
                console.error('KoalaForms analytics save error', e);
            } finally {
                this.isSaving = false;
            }
        },
        addChart(config) {
            this.showAddModal = false;
            this.charts.push(config);
            this.fetchAllChartData();
            this.scheduleSave();
        },
        removeChart(id) {
            this.charts = this.charts.filter(c => c.id !== id);
            this.scheduleSave();
        },
        updateChartDays(id, days) {
            const chart = this.charts.find(c => c.id === id);
            if (!chart || chart.days === days) return;
            chart.days = days;
            this.fetchAllChartData();
            this.scheduleSave();
        },
        scheduleSave() {
            clearTimeout(this.saveTimer);
            this.saveTimer = setTimeout(() => this.saveConfig(), 800);
        },
    },
};
</script>

<style>
.kf-analytics-page {
    max-width: 1200px;
    padding: 20px 24px;
}
.kf-analytics-page__title {
    font-size: 22px;
    font-weight: 700;
    color: #111827;
    margin: 0 0 24px;
}
.kf-analytics-page__empty {
    padding: 48px 0;
    text-align: center;
    color: #6b7280;
    font-size: 14px;
}
.kf-chart-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(480px, 1fr));
    gap: 20px;
}
</style>
