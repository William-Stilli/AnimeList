<script setup>
import { ref, watch } from 'vue';
import { router, Head, Link } from '@inertiajs/vue3';
import debounce from 'lodash/debounce';
import AppLayout from '@/layouts/AppLayout.vue';

const props = defineProps({
    mangas: Array,
    filters: Object,
    apiError: String
});

const search = ref(props.filters.search || '');

watch(search, debounce((value) => {
    router.get(route('mangas.search'), { search: value }, {
        preserveState: true,
        replace: true
    });
}, 500));

const addManga = (manga) => {
    console.log("Tentative d'ajout pour :", manga.title);

    router.post(route('mangas.store'), {
        mal_id: manga.mal_id,
        title: manga.title,
        image_url: manga.images?.jpg?.image_url
    }, {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            alert(manga.title + ' a été ajouté avec succès à ta collection !');
        },
        onError: (errors) => {
            alert('Oups, le backend a bloqué : ' + Object.values(errors).join(' | '));
            console.error("Détail des erreurs :", errors);
        }
    });
};
</script>

<template>
    <Head title="Recherche de Mangas" />
    <AppLayout>
    <div class="min-h-screen flex flex-col font-sans">
        
        <div class="bg-white pt-12 pb-10 px-8 flex flex-col items-center">
            <h1 class="text-3xl font-bold text-gray-900 mb-8 tracking-tight">Trouvez votre prochain manga</h1>
            
            <div class="relative w-full max-w-2xl">
                <div class="absolute inset-y-0 left-5 flex items-center pointer-events-none">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <input
                    v-model="search"
                    type="text"
                    placeholder="Chercher une romance tranquille..."
                    class="w-full pl-14 pr-6 py-4 rounded-full border border-gray-200 shadow-[0_4px_25px_rgba(139,92,246,0.15)] text-gray-800 text-lg focus:outline-none focus:ring-2 focus:ring-blue-500/20 transition-shadow"
                />
            </div>
        </div>

        <div class="flex-1 bg-[#8c8c8c] p-8">
            
            <div v-if="apiError" class="bg-red-900/50 border border-red-500 text-red-200 p-4 rounded-lg mb-8 flex items-center gap-3">
                <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                <span>Oups ! {{ apiError }}</span>
            </div>

            <div v-if="mangas.length > 0" class="mb-6 flex items-center text-gray-600 text-sm font-bold uppercase tracking-widest">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                RÉSULTATS ({{ mangas.length }})
            </div>

            <div v-if="!mangas.length && search.length === 0" class="text-center py-20 text-gray-700 font-medium">
                Commencez à taper pour chercher un manga sur Tenrai.
            </div>
            <div v-else-if="!mangas.length && search.length > 0" class="text-center py-20 text-gray-700 font-medium">
                Aucun manga trouvé pour "<span class="text-gray-900 font-bold">{{ search }}</span>".
            </div>

            <div v-else class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-5 gap-x-6 gap-y-10">
                <div v-for="manga in mangas" :key="manga.mal_id" class="flex flex-col group relative">
                    
                    <div class="relative aspect-[2/3] rounded-2xl overflow-hidden shadow-lg mb-3 bg-gray-400">
                        <Link :href="route('mangas.show', manga.mal_id)" class="block w-full h-full">
                            <img :src="manga.images?.jpg?.image_url" :alt="manga.title" class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105">
                        </Link>

                        <div v-if="manga.score" class="absolute top-3 right-3 bg-gray-900/90 backdrop-blur-sm text-[11px] px-2 py-1 rounded-md font-bold text-white flex items-center gap-1 shadow-md">
                            <span class="text-yellow-400">⭐</span> {{ manga.score }}
                        </div>

                        <button 
                            type="button"
                            @click.prevent.stop="addManga(manga)" 
                            class="cursor-pointer absolute bottom-3 right-3 p-3 bg-blue-600 hover:bg-blue-500 rounded-full shadow-lg border border-white/10 transition-all duration-300 transform translate-y-12 opacity-0 group-hover:translate-y-0 group-hover:opacity-100 focus:outline-none z-50" 
                            title="Ajouter à la bibliothèque"
                        >
                            <svg class="w-5 h-5 text-white pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                        </button>
                    </div>

                    <h2 class="text-gray-900 font-bold text-base leading-tight truncate px-1" :title="manga.title">
                        {{ manga.title }}
                    </h2>
                    
                    <div class="text-gray-700 text-xs font-medium mt-1 px-1">
                        {{ manga.published?.prop?.from?.year || 'N/A' }} • {{ manga.type || 'Manga' }}
                    </div>

                </div>
            </div>

        </div>
    </div>
</AppLayout>
</template>