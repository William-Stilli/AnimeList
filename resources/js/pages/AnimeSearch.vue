<script setup>
import { ref } from 'vue';
import axios from 'axios';
import AppLayout from '@/layouts/AppLayout.vue';
import AnimeToast from '@/components/AnimeToast.vue';
import { useToast } from 'vue-toastification';

const toast = useToast();

const query = ref('');
const results = ref([]);
const loading = ref(false);

const searchAnime = async () => {
    if (query.value.length < 3) return;

    loading.value = true;
    try {
        const response = await axios.get(`https://api.jikan.moe/v4/anime?q=${query.value}&limit=10`);
        results.value = response.data.data;
    } catch (error) {
        console.error("L'API a planté :", error);
    } finally {
        loading.value = false;
    }
};


const addToLibrary = async (anime) => {
    try {
        const allGenres = [
            ...(anime.genres || []),
            ...(anime.demographics || [])
        ]

        const payload = {
            mal_id: anime.mal_id,
            title: anime.title,
            title_english: anime.title_english,
            image_url: anime.images.jpg.image_url,
            episodes: anime.episodes,
            genres: allGenres
        };

        const response = await axios.post('/animes', payload);

        toast.success(
            {
                component: AnimeToast,
                props: {
                    title: anime.title,
                    image: anime.images.jpg.image_url,
                    message: `L'anime ${anime.title} a été ajouté à votre liste`
                },
            },
            {
                timeout: 3500,
                icon: false,
            }
        );

    } catch (error) {
        console.error(error);
        toast.error("Erreur : Impossible d'ajouter cet animé");
    }
};
</script>

<template>
    <AppLayout>
        <div class="p-6 max-w-4xl mx-auto">
            <div class="flex gap-4 mb-8">
                <input v-model="query" @keyup.enter="searchAnime" type="text"
                    placeholder="Rechercher un animé (ex: Steins;Gate)..."
                    class="w-full p-2 border border-gray-300 rounded shadow-sm focus:ring-2 focus:ring-blue-500 outline-none" />
                <button @click="searchAnime"
                    class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 transition" :disabled="loading">
                    {{ loading ? 'Chargement...' : 'Chercher' }}
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div v-for="anime in results" :key="anime.mal_id"
                    class="flex gap-4 p-4 bg-white rounded-lg shadow hover:shadow-md transition border border-gray-100">
                    <img :src="anime.images.jpg.image_url" :alt="anime.title" class="w-24 h-36 object-cover rounded">

                    <div class="flex flex-col justify-between flex-1">
                        <div>
                            <h3 class="font-bold text-lg text-gray-500 leading-tight">
                                {{ anime.title_english || anime.title }}
                            </h3>
                            <p v-if="anime.title_english" class="text-xs text-gray-400">{{ anime.title }}</p>
                            <p class="text-sm text-gray-500 mt-1">{{ anime.type }} - {{ anime.year || '?' }}</p>
                        </div>

                        <button @click="addToLibrary(anime)"
                            class="self-start mt-2 text-sm bg-green-100 text-green-700 px-3 py-1 rounded hover:bg-green-200">
                            + Ajouter à ma liste
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>