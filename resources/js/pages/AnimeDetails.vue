<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';

const props = defineProps({
    anime: Object
});

const jikanData = ref(null);
const loading = ref(null);

const myData = computed(() => {
    return props.anime.users.length > 0 ? props.anime.users[0].pivot : null;
});

onMounted(async () => {
    if (props.anime.mal_id) {
        try {
            const response = await axios.get(`https://api.jikan.moe/v4/anime/${props.anime.mal_id}`);
            jikanData.value = response.data.data;
        } catch (error) {
            console.error("Erreur Jikan", e);
        } finally {
            loading.value = false;
        }
    }
});
</script>

<template>

    <Head :title="anime.title" />

    <AppLayout>
        <div class="relative h-64 overflow-hidden bg-gray-900">
            <img v-if="anime.image_url" :src="anime.image_url" class="w-full h-full object-cover opacity-30 blur-sm">
            <div class="absolute inset-0 flex items-center justify-center">
                <h1 class="text-4xl font-bold text-white text-center shadow-black drop-shadow-lg px-4">
                    {{ anime.title_english || anime.title }}
                </h1>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-20 relative z-10 pb-12">
            <div class="bg-white rounded-xl shadow-xl overflow-hidden flex flex-col md:flex-row">

                <div class="md:w-1/3 lg:w-1/4 bg-gray-50 p-6 flex flex-col items-center border-r border-gray-100">
                    <img :src="anime.image_url" class="w-48 rounded-lg shadow-lg mb-6 border-4 border-white">

                    <div v-if="myData" class="w-full bg-white p-4 rounded-lg shadow-sm border border-gray-200 mb-4">
                        <h3 class="font-bold text-gray-700 border-b pb-2 mb-2 text-center">Ma Progression</h3>
                        <div class="flex justify-between text-sm mb-1">
                            <span>Statut:</span>
                            <span class="font-bold text-blue-600">{{ myData.status }}</span>
                        </div>
                        <div class="flex justify-between text-sm mb-1">
                            <span>Score:</span>
                            <span class="font-bold text-yellow-600">★ {{ myData.score }}/10</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span>Épisodes:</span>
                            <span class="font-bold">{{ myData.progress }} / {{ anime.episodes || '?' }}</span>
                        </div>
                    </div>

                    <Link :href="route('library')" class="text-blue-600 hover:underline text-sm mt-4">
                        ← Retour à la bibliothèque
                    </Link>
                </div>

                <div class="md:w-2/3 lg:w-3/4 p-8">
                    <div v-if="loading" class="animate-pulse space-y-4">
                        <div class="h-4 bg-gray-200 rounded w-3/4"></div>
                        <div class="h-4 bg-gray-200 rounded w-1/2"></div>
                        <div class="h-32 bg-gray-200 rounded"></div>
                    </div>

                    <div v-else-if="jikanData">
                        <div class="flex gap-2 mb-4">
                            <span v-for="genre in jikanData.genres" :key="genre.mal_id"
                                class="px-3 py-1 bg-gray-100 text-gray-600 text-xs rounded-full font-bold uppercase tracking-wide">
                                {{ genre.name }}
                            </span>
                        </div>

                        <h2 class="text-2xl font-bold text-gray-800 mb-4">Synopsis</h2>
                        <p class="text-gray-600 leading-relaxed mb-6 whitespace-pre-line">
                            {{ jikanData.synopsis }}
                        </p>

                        <div v-if="jikanData.trailer?.embed_url" class="mt-8">
                            <h3 class="font-bold text-gray-800 mb-3">Trailer</h3>
                            <iframe class="w-full aspect-video rounded-lg shadow-md" :src="jikanData.trailer.embed_url"
                                allowfullscreen></iframe>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AppLayout>
</template>