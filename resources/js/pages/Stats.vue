<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';

import { Pie, Bar } from 'vue-chartjs';
import {
    Chart as ChartJS,
    Title,
    Tooltip,
    Legend,
    ArcElement,
    BarElement,
    CategoryScale,
    LinearScale,
    RadialLinearScale
} from 'chart.js';
import { PolarArea } from 'vue-chartjs';

ChartJS.register(Title, Tooltip, Legend, ArcElement, BarElement, CategoryScale, LinearScale, RadialLinearScale);

const props = defineProps({
    statusData: Array,
    scoreData: Array,
    genreData: Array
});

const pieData = computed(() => {
    const statusColors = {
        watching: '#3B82F6',
        completed: '#10B981',
        plan_to_watch: '#9CA3AF',
        dropped: '#EF4444'
    };

    const labels = props.statusData.map(d => d.status);
    const data = props.statusData.map(d => d.total);
    const backgroundColor = props.statusData.map(d => statusColors[d.status] || '#000');

    return {
        labels: labels,
        datasets: [{
            backgroundColor: backgroundColor,
            data: data
        }]
    };
});

const pieOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { position: 'bottom' }
    }
};

const barData = computed(() => {
    const allScores = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
    const dataMap = {};

    props.scoreData.forEach(item => {
        dataMap[item.score] = item.total;
    });

    return {
        labels: allScores,
        datasets: [{
            label: 'Nombre d\'animés',
            backgroundColor: '#F59E0B',
            data: allScores.map(score => dataMap[score] || 0),
            borderRadius: 4
        }]
    };
});

const barOptions = {
    responsive: true,
    maintainAspectRatio: false,
    scales: {
        y: { beginAtZero: true, ticks: { stepSize: 1 } }
    }
};

const genreChartData = computed(() => {
    return {
        labels: props.genreData.map(d => d.name),
        datasets: [{
            label: 'Genres favoris',
            data: props.genreData.map(d => d.total),
            backgroundColor: [
                'rgba(255, 99, 132, 0.7)',
                'rgba(54, 162, 235, 0.7)',
                'rgba(255, 206, 86, 0.7)',
                'rgba(75, 192, 192, 0.7)',
                'rgba(153, 102, 255, 0.7)',
                'rgba(255, 159, 64, 0.7)',
            ],
            borderWidth: 1
        }]
    };
});

const genreOptions = {
    responsive: true,
    scales: {
        r: { ticks: { display: false } }
    }
};
</script>

<template>

    <Head title="Mes Statistiques" />

    <AppLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Mes Statistiques</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-bold text-gray-700 mb-4 text-center">État de la collection</h3>
                        <div class="h-64 relative">
                            <Pie :data="pieData" :options="pieOptions" />
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-bold text-gray-700 mb-4 text-center">Distribution des notes</h3>
                        <div class="h-64 relative">
                            <Bar :data="barData" :options="barOptions" />
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 md:col-span-2 lg:col-span-1">
                        <h3 class="text-lg font-bold text-gray-700 mb-4 text-center">Genres Favoris</h3>
                        <div class="h-64 relative">
                            <PolarArea :data="genreChartData" :options="genreOptions" />
                        </div>
                        <p v-if="genreData.length === 0" class="text-xs text-center text-gray-400 mt-4">
                            (Données insuffisantes : Ajoute des nouveaux animés pour voir tes genres !)
                        </p>
                    </div>

                </div>

                <div class="mt-8 bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 text-center">
                    <p class="text-gray-600">
                        Tu as noté <strong>{{scoreData.reduce((acc, curr) => acc + curr.total, 0)}}</strong> animés au
                        total.
                    </p>
                </div>

            </div>
        </div>
    </AppLayout>
</template>