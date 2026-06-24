<template>
    <div class="kf-chart-card">
        <div class="kf-chart-card__header">
            <span class="kf-chart-card__title">{{ cardTitle }}</span>
            <div class="kf-chart-card__controls">
                <select
                    class="kf-chart-card__days"
                    :value="config.days"
                    @change="$emit('update-days', +$event.target.value)"
                >
                    <option value="7">Last 7 days</option>
                    <option value="30">Last 30 days</option>
                    <option value="60">Last 60 days</option>
                    <option value="90">Last 90 days</option>
                </select>
                <button class="kf-chart-card__remove" @click="$emit('remove')" title="Remove chart">&times;</button>
            </div>
        </div>

        <div v-if="isLoading" class="kf-chart-card__skeleton"></div>

        <template v-else-if="hasData">
            <ChartSubmissionsOverTime v-if="config.type === 'submissionsOverTime'" :chartData="chartData" :days="config.days" />
            <ChartStageDistribution   v-else-if="config.type === 'stageDistribution'"   :chartData="chartData" />
            <ChartBrowserBreakdown    v-else-if="config.type === 'browserBreakdown'"    :chartData="chartData" />
            <ChartFieldDistribution   v-else-if="config.type === 'fieldDistribution'"   :chartData="chartData" :fieldLabel="config.field_label || 'Field'" />
        </template>

        <div v-else class="kf-chart-card__empty">
            No data available for the selected period.
        </div>
    </div>
</template>

<script>
import ChartSubmissionsOverTime from './ChartSubmissionsOverTime.vue';
import ChartStageDistribution   from './ChartStageDistribution.vue';
import ChartBrowserBreakdown    from './ChartBrowserBreakdown.vue';
import ChartFieldDistribution   from './ChartFieldDistribution.vue';

const TYPE_LABELS = {
    submissionsOverTime: 'Submissions Over Time',
    stageDistribution:   'Stage Distribution',
    browserBreakdown:    'Browser Breakdown',
    fieldDistribution:   'Field Distribution',
};

export default {
    name: 'ChartCard',
    components: {
        ChartSubmissionsOverTime,
        ChartStageDistribution,
        ChartBrowserBreakdown,
        ChartFieldDistribution,
    },
    emits: ['remove', 'update-days'],
    props: {
        config:    { type: Object,  required: true },
        chartData: { type: Array,   default: () => null },
        isLoading: { type: Boolean, default: false },
    },
    computed: {
        cardTitle() {
            const base = TYPE_LABELS[this.config.type] || this.config.type;
            if (this.config.type === 'fieldDistribution' && this.config.field_label) {
                return `${base}: ${this.config.field_label}`;
            }
            return base;
        },
        hasData() {
            return Array.isArray(this.chartData) && this.chartData.length > 0;
        },
    },
};
</script>

<style>
.kf-chart-card {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 16px;
    display: flex;
    flex-direction: column;
    gap: 12px;
}
.kf-chart-card__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
}
.kf-chart-card__controls {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-shrink: 0;
}
.kf-chart-card__days {
    font-size: 12px;
    padding: 3px 6px;
    border: 1px solid #d1d5db;
    border-radius: 4px;
    color: #374151;
    background: #f9fafb;
    cursor: pointer;
}
.kf-chart-card__title {
    font-weight: 600;
    font-size: 14px;
    color: #111827;
}
.kf-chart-card__remove {
    background: none;
    border: none;
    cursor: pointer;
    font-size: 18px;
    color: #9ca3af;
    line-height: 1;
    padding: 0 4px;
}
.kf-chart-card__remove:hover { color: #ef4444; }
.kf-chart-card__skeleton {
    height: 180px;
    background: linear-gradient(90deg, #f3f4f6 25%, #e5e7eb 50%, #f3f4f6 75%);
    background-size: 200% 100%;
    animation: kf-shimmer 1.4s infinite;
    border-radius: 6px;
}
@keyframes kf-shimmer {
    0%   { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}
.kf-chart-card__empty {
    text-align: center;
    color: #6b7280;
    font-size: 13px;
    padding: 40px 0;
}
</style>
