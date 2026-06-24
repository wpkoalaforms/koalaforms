<template>
    <canvas ref="canvas"></canvas>
</template>

<script>
import {
    Chart,
    BarController,
    BarElement,
    CategoryScale,
    LinearScale,
    Tooltip,
    Legend,
} from 'chart.js';

Chart.register(BarController, BarElement, CategoryScale, LinearScale, Tooltip, Legend);

export default {
    name: 'ChartFieldDistribution',
    props: {
        chartData:  { type: Array,  default: () => [] },
        fieldLabel: { type: String, default: 'Field' },
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
        chartData:  { deep: true, handler() { this.buildChart(); } },
        fieldLabel: { handler() { this.buildChart(); } },
    },
    methods: {
        buildChart() {
            this.chart?.destroy();
            const labels = this.chartData.map(r => r.value || '(empty)');
            const data   = this.chartData.map(r => r.count);
            this.chart = new Chart(this.$refs.canvas, {
                type: 'bar',
                data: {
                    labels,
                    datasets: [{
                        label:           this.fieldLabel,
                        data,
                        backgroundColor: 'rgba(59,130,246,0.7)',
                        borderColor:     'rgba(59,130,246,1)',
                        borderWidth:     1,
                    }],
                },
                options: {
                    indexAxis:  'y',
                    responsive: true,
                    plugins: { legend: { display: false } },
                    scales:  { x: { beginAtZero: true, ticks: { precision: 0 } } },
                },
            });
        },
    },
};
</script>
