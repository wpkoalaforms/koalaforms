<template>
    <canvas ref="canvas"></canvas>
</template>

<script>
import {
    Chart,
    DoughnutController,
    ArcElement,
    Tooltip,
    Legend,
} from 'chart.js';

Chart.register(DoughnutController, ArcElement, Tooltip, Legend);

const PALETTE = [
    '#3b82f6','#10b981','#f59e0b','#ef4444','#8b5cf6',
    '#06b6d4','#f97316','#6366f1','#ec4899','#14b8a6',
];

export default {
    name: 'ChartStageDistribution',
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
            const labels = this.chartData.map(r => r.stage || 'Unknown');
            const data   = this.chartData.map(r => r.count);
            this.chart = new Chart(this.$refs.canvas, {
                type: 'doughnut',
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
