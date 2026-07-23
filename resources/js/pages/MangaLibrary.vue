<script setup>
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    mangas: Array
});
</script>

<template>
    <Head title="Ma Bibliothèque Manga" />

    <div class="min-h-screen flex flex-col font-sans">
        
        <div class="bg-white pt-12 pb-10 px-8 flex flex-col items-center shadow-sm z-10">
            <h1 class="text-3xl font-bold text-gray-900 mb-2 tracking-tight">Ma Bibliothèque Manga</h1>
            <p class="text-gray-500 font-medium">Gère ta collection et suis ta progression</p>
        </div>

        <div class="flex-1 bg-[#8c8c8c] p-8">
            
            <div class="mb-6 flex items-center justify-between text-gray-600 text-sm font-bold uppercase tracking-widest">
                <div class="flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                    MA COLLECTION ({{ mangas.length }})
                </div>
                
                <Link :href="route('mangas.search')" class="bg-white/20 hover:bg-white/40 text-gray-800 px-4 py-2 rounded-full transition-colors flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    Chercher
                </Link>
            </div>

            <div v-if="!mangas.length" class="text-center py-20 bg-gray-400/20 rounded-2xl border-2 border-dashed border-gray-500">
                <p class="text-gray-700 font-medium text-lg mb-4">Ta bibliothèque est complètement vide !</p>
                <Link :href="route('mangas.search')" class="inline-block bg-blue-600 hover:bg-blue-500 text-white font-bold py-2 px-6 rounded-full shadow-lg transition-transform hover:-translate-y-1">
                    Ajouter mon premier manga
                </Link>
            </div>

            <div v-else class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-x-6 gap-y-10">
                <div v-for="manga in mangas" :key="manga.id" class="flex flex-col group relative">
                    
                    <div class="relative aspect-[2/3] rounded-2xl overflow-hidden shadow-lg mb-3 bg-gray-400">
                        <Link :href="route('mangas.show', manga.mal_id)" class="block w-full h-full">
                            <img :src="manga.image_url" :alt="manga.title" class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105">
                        </Link>

                        <div class="absolute top-3 right-3 bg-gray-900/90 backdrop-blur-sm text-[11px] px-2 py-1 rounded-md font-bold text-white shadow-md">
                            {{ manga.status }}
                        </div>
                    </div>

                    <h2 class="text-gray-900 font-bold text-base leading-tight truncate px-1" :title="manga.title">
                        {{ manga.title }}
                    </h2>
                    
                    <div class="text-gray-700 text-xs font-medium mt-1 px-1 flex justify-between">
                        <span>Ch: {{ manga.chapters_read }}</span>
                        <span>Vol: {{ manga.volumes_owned }}</span>
                    </div>

                </div>
            </div>

        </div>
    </div>
</template>