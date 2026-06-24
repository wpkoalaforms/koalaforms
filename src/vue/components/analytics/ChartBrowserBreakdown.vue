<template>
    <canvas ref="canvas"></canvas>
</template>

<script>
import {
    Chart,
    PieController,
    ArcElement,
    Tooltip,
    Legend,
} from 'chart.js';

Chart.register(PieController, ArcElement, Tooltip, Legend);

const PALETTE = [
    '#6366f1','#3b82f6','#f59e0b','#10b981','#ef4444',
    '#8b5cf6','#06b6d4','#f97316','#ec4899','#14b8a6',
];

export default {
    name: 'ChartBrowserBreakdown',
    props: {
        chartData: { type: Array, default: () => [] },
    },
    data() {
        return { chart: null };
    },
    mounted() {
        this.buildChart();
    },
    beforeUnmount() {
        this.chart?.destroy();
    },
    watch: {
        chartData: { deep: true, handler() { this.buildChart(); } },
    },
    methods: {
        buildChart() {
            this.chart?.destroy();
            const labels = this.chartData.map(r => r.browser || 'Unknown');
            const data   = this.chartData.map(r => r.count);
            this.chart = new Chart(this.$refs.canvas, {
                type: 'pie',
                data: {
                    labels,
                    datasets: [{
                        data,
                        backgroundColor: PALETTE.slice(0, data.length),
                        hoverOffset: 6,
                    }],
                },
                options: {
                    responsive: true,
                    plugins: { legend: { position: 'bottom' } },
                },
            });
        },
    },
};
</script>
