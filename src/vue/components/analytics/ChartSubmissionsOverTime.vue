<template>
    <canvas ref="canvas"></canvas>
</template>

<script>
import {
    Chart,
    LineController,
    LineElement,
    PointElement,
    CategoryScale,
    LinearScale,
    Tooltip,
    Legend,
    Filler,
} from 'chart.js';

Chart.register(LineController, LineElement, PointElement, CategoryScale, LinearScale, Tooltip, Legend, Filler);

export default {
    name: 'ChartSubmissionsOverTime',
    props: {
        chartData: { type: Array, default: () => [] },
        days:      { type: Number, default: 30 },
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
            const { labels, counts } = this.fillDates();
            this.chart = new Chart(this.$refs.canvas, {
                type: 'line',
                data: {
                    labels,
                    datasets: [{
                        label:           'Submissions',
                        data:            counts,
                        fill:            true,
                        tension:         0.3,
                        backgroundColor: 'rgba(59,130,246,0.15)',
                        borderColor:     'rgba(59,130,246,1)',
                        pointRadius:     3,
                    }],
                },
                options: {
                    responsive: true,
                    plugins: { legend: { display: false } },
                    scales:  { y: { beginAtZero: true, ticks: { precision: 0 } } },
                },
            });
        },
        fillDates() {
            const map = {};
            for (const row of this.chartData) {
                map[row.date] = row.count;
            }
            const labels = [];
            const counts = [];
            for (let i = this.days - 1; i >= 0; i--) {
                const d = new Date();
                d.setDate(d.getDate() - i);
                const key = d.toISOString().slice(0, 10);
                labels.push(key);
                counts.push(map[key] ?? 0);
            }
            return { labels, counts };
        },
    },
};
</script>
