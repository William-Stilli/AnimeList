<template>
    <div class="relative w-full h-full min-h-[250px] flex justify-center items-center drop-shadow-sm">
        <Radar :data="chartData" :options="chartOptions" />
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { Radar } from 'vue-chartjs';
import {
    Chart as ChartJS,
    RadialLinearScale,
    PointElement,
    LineElement,
    Filler,
    Tooltip,
    Legend
} from 'chart.js';

ChartJS.register(RadialLinearScale, PointElement, LineElement, Filler, Tooltip, Legend);

const props = defineProps({
    labels: {
        type: Array,
        required: true
    },
    values: {
        type: Array,
        required: true
    }
});

const chartData = computed(() => ({
    labels: props.labels,
    datasets: [
        {
            label: 'Animés vus',
            data: props.values,
            backgroundColor: 'rgba(139, 92, 246, 0.35)',
            borderColor: 'rgba(139, 92, 246, 1)',
            borderWidth: 3,
            pointBackgroundColor: '#ffffff',
            pointBorderColor: 'rgba(139, 92, 246, 1)',
            pointBorderWidth: 2,
            pointRadius: 4,
            pointHoverRadius: 7,
            pointHoverBackgroundColor: 'rgba(139, 92, 246, 1)',
            pointHoverBorderColor: '#ffffff',
            pointHoverBorderWidth: 3,
            tension: 0.4
        }
    ]
}));

const chartOptions = computed(() => {
    const maxVal = Math.max(...(props.values.length ? props.values : [1]));

    return {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            r: {
                min: -(maxVal * 0.1),
                beginAtZero: false,

                angleLines: { color: 'rgba(0, 0, 0, 0.05)', lineWidth: 1 },
                grid: { color: 'rgba(0, 0, 0, 0.05)', circular: false },
                pointLabels: {
                    font: { size: 12, weight: '900', family: "'Inter', system-ui, sans-serif" },
                    color: '#374151',
                    padding: 15
                },
                ticks: { display: false, maxTicksLimit: 5 }
            }
        },
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: 'rgba(17, 24, 39, 0.95)',
                titleFont: { size: 13, family: "'Inter', sans-serif", weight: 'normal' },
                bodyFont: { size: 16, weight: 'bold' },
                padding: 12,
                cornerRadius: 12,
                displayColors: false,
                callbacks: {
                    label: function (context) {
                        return `🔥 ${context.raw} œuvres visionnées`;
                    }
                }
            }
        },
        animation: { duration: 1500, easing: 'easeOutQuart' }
    };
});
</script>