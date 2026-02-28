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
} from 'chart.js';

ChartJS.register(Title, Tooltip, Legend, ArcElement, BarElement, CategoryScale, LinearScale);

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
        dropped: '#EF4444',
        on_hold: '#F59E0B'
    };

    const formatLabel = (status) => {
        const map = {
            'watching': 'En cours',
            'completed': 'Terminé',
            'plan_to_watch': 'À voir',
            'dropped': 'Abandonné',
            'on_hold': 'En pause'
        };
        return map[status] || status;
    };

    const labels = props.statusData.map(d => formatLabel(d.status));
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
            label: 'Animés',
            backgroundColor: '#8B5CF6',
            data: allScores.map(score => dataMap[score] || 0),
            borderRadius: 4,
            barPercentage: 0.7
        }]
    };
});

const barOptions = {
    responsive: true,
    maintainAspectRatio: false,
    scales: {
        y: { beginAtZero: true, ticks: { stepSize: 1 }, grid: { color: '#f3f4f6' } },
        x: { grid: { display: false } }
    },
    plugins: { legend: { display: false } }
};

const dynamicChartHeight = computed(() => {
    const minHeight = 400;
    const calculatedHeight = props.genreData.length * 35; 
    return Math.max(minHeight, calculatedHeight);
});

const genreChartData = computed(() => {
    const sortedGenres = [...props.genreData].sort((a, b) => b.total - a.total);

    const backgroundColors = [
        '#FF6384', // Rouge Rosé
        '#36A2EB', // Bleu Ciel
        '#FFCE56', // Jaune
        '#4BC0C0', // Turquoise
        '#9966FF', // Violet
        '#FF9F40', // Orange
        '#C9CBCF', // Gris
        '#E7E9ED', // Gris clair
        '#76A346', // Vert Olive
        '#D36E70', // Rouge pâle
        '#8B5CF6', // Violet Intense
        '#EC4899', // Rose Bonbon
        '#10B981', // Vert Emeraude
        '#F59E0B', // Ambre
        '#6366F1'  // Indigo
    ];

    return {
        labels: sortedGenres.map(d => d.name),
        datasets: [{
            label: 'Animés vus',
            data: sortedGenres.map(d => d.total),
            backgroundColor: backgroundColors, 
            borderRadius: 4,
            barThickness: 20,
        }]
    };
});

const genreOptions = {
    indexAxis: 'y', 
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: false },
        tooltip: {
            callbacks: { label: (c) => ` ${c.raw} animés` }
        }
    },
    scales: {
        x: { beginAtZero: true, grid: { color: '#f3f4f6' }, position: 'top' },
        y: { 
            grid: { display: false },
            ticks: { autoSkip: false } 
        }
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
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 flex flex-col">
                        <h3 class="text-lg font-bold text-gray-700 mb-4 text-center">État de la collection</h3>
                        <div class="h-64 relative w-full">
                            <Pie :data="pieData" :options="pieOptions" />
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 flex flex-col">
                        <h3 class="text-lg font-bold text-gray-700 mb-4 text-center">Distribution des notes</h3>
                        <div class="h-64 relative w-full">
                            <Bar :data="barData" :options="barOptions" />
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-bold text-gray-700">Tous les Genres</h3>
                        <span class="text-xs font-semibold bg-gray-100 text-gray-500 py-1 px-3 rounded-full">
                            Total : {{ genreData.length }}
                        </span>
                    </div>

                    <div class="h-96 overflow-y-auto border border-gray-100 rounded-lg pr-2 scrollbar-thin scrollbar-thumb-gray-300">
                        <div :style="{ height: dynamicChartHeight + 'px', position: 'relative' }">
                            <Bar :data="genreChartData" :options="genreOptions" />
                        </div>
                    </div>
                    
                    <p class="text-center text-xs text-gray-400 mt-2">
                        Scrolle pour voir toute la liste !
                    </p>

                    <p v-if="genreData.length === 0" class="text-sm text-center text-gray-400 mt-4">
                        (Données insuffisantes : Lance la commande "php artisan anime:fix-genres" !)
                    </p>
                </div>

            </div>
        </div>
    </AppLayout>
</template>