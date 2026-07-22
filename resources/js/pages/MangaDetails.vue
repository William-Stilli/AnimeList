<script setup>
import { ref } from 'vue';
import { Head, router, Link } from '@inertiajs/vue3';

const props = defineProps({
    manga: Object,
    inLibrary: Boolean
});

const isAdding = ref(false);

const addManga = () => {
    if (props.inLibrary || isAdding.value) return;
    
    isAdding.value = true;
    router.post(route('mangas.store'), {
        mal_id: props.manga.mal_id,
        title: props.manga.title,
        image_url: props.manga.images?.jpg?.large_image_url || props.manga.images?.jpg?.image_url
    }, {
        preserveScroll: true,
        onSuccess: () => {
            alert(props.manga.title + ' ajouté à ta collection !');
        },
        onFinish: () => isAdding.value = false
    });
};
</script>

<template>
    <Head :title="manga.title" />

    <div class="min-h-screen bg-[#121212] text-white p-8">
        
        <Link :href="route('mangas.search')" class="inline-flex items-center text-gray-400 hover:text-white mb-8 transition-colors">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Retour à la recherche
        </Link>

        <div class="flex flex-col md:flex-row gap-10 max-w-6xl mx-auto">
            
            <div class="w-full md:w-1/3 lg:w-1/4 shrink-0">
                <img :src="manga.images?.jpg?.large_image_url" :alt="manga.title" class="w-full rounded-xl shadow-2xl shadow-black/50 mb-6">
                
                <button 
                    @click="addManga" 
                    :disabled="inLibrary || isAdding"
                    class="w-full py-3 rounded-lg font-bold transition-all flex justify-center items-center gap-2"
                    :class="inLibrary ? 'bg-green-600/20 text-green-500 cursor-not-allowed border border-green-600/30' : 'bg-blue-600 hover:bg-blue-500 text-white shadow-lg'"
                >
                    <template v-if="inLibrary">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        Déjà dans la bibliothèque
                    </template>
                    <template v-else>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        Ajouter à ma collection
                    </template>
                </button>
            </div>

            <div class="flex-1">
                <h1 class="text-4xl font-bold mb-2">{{ manga.title }}</h1>
                <h2 v-if="manga.title_japanese" class="text-xl text-gray-500 mb-6 font-light">{{ manga.title_japanese }}</h2>

                <div class="flex flex-wrap gap-4 mb-8">
                    <div class="bg-[#1e1e1e] border border-gray-800 px-4 py-2 rounded-lg text-center">
                        <div class="text-gray-400 text-xs uppercase font-bold tracking-wider mb-1">Score</div>
                        <div class="text-yellow-400 font-bold text-lg">⭐ {{ manga.score || 'N/A' }}</div>
                    </div>
                    <div class="bg-[#1e1e1e] border border-gray-800 px-4 py-2 rounded-lg text-center">
                        <div class="text-gray-400 text-xs uppercase font-bold tracking-wider mb-1">Statut</div>
                        <div class="text-white text-lg">{{ manga.status }}</div>
                    </div>
                    <div class="bg-[#1e1e1e] border border-gray-800 px-4 py-2 rounded-lg text-center">
                        <div class="text-gray-400 text-xs uppercase font-bold tracking-wider mb-1">Chapitres</div>
                        <div class="text-white text-lg">{{ manga.chapters || '?' }}</div>
                    </div>
                    <div class="bg-[#1e1e1e] border border-gray-800 px-4 py-2 rounded-lg text-center">
                        <div class="text-gray-400 text-xs uppercase font-bold tracking-wider mb-1">Volumes</div>
                        <div class="text-white text-lg">{{ manga.volumes || '?' }}</div>
                    </div>
                </div>

                <div class="mb-8 flex flex-wrap gap-2">
                    <span v-for="genre in manga.genres" :key="genre.mal_id" class="px-3 py-1 bg-gray-800 text-gray-300 rounded-full text-sm">
                        {{ genre.name }}
                    </span>
                </div>

                <div>
                    <h3 class="text-xl font-bold mb-4 border-b border-gray-800 pb-2">Synopsis</h3>
                    <p class="text-gray-300 leading-relaxed whitespace-pre-line">
                        {{ manga.synopsis || "Aucun synopsis disponible." }}
                    </p>
                </div>

            </div>
        </div>
    </div>
</template>