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
    <div class="min-h-screen bg-[#121212] text-white p-8">
        
        <header class="mb-10">
            <h1 class="text-3xl font-bold mb-2">Trouver un Manga</h1>
            <p class="text-gray-400 text-sm">Ajoute de nouvelles lectures à ta collection.</p>
        </header>

        <div class="mb-10 max-w-xl">
            <div class="relative">
                <input
                    v-model="search"
                    type="text"
                    placeholder="Chercher un manga..."
                    class="w-full bg-[#1e1e1e] border border-gray-700 rounded-lg py-3 px-4 text-white placeholder-gray-500 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
                />
                <svg class="w-5 h-5 absolute right-4 top-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>
        </div>

        <div v-if="apiError" class="bg-red-900/50 border border-red-500 text-red-200 p-4 rounded-lg mb-8 flex items-center gap-3">
            <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            <span>Oups ! {{ apiError }}</span>
        </div>

        <div v-else-if="!mangas.length && search.length === 0" class="text-center py-20 text-gray-500">
            Commence à taper le nom d'un manga pour lancer la recherche sur Tenrai.
        </div>

        <div v-else-if="!mangas.length && search.length > 0" class="text-center py-20 text-gray-500">
            Aucun manga trouvé pour "<span class="text-gray-300">{{ search }}</span>".
        </div>

        <div v-else class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-6">
            <div v-for="manga in mangas" :key="manga.mal_id" class="bg-[#1e1e1e] rounded-xl overflow-hidden shadow-lg hover:shadow-blue-900/20 transition-all hover:-translate-y-1 group cursor-pointer">
                
                <div class="relative h-64 overflow-hidden">
                    <Link :href="route('mangas.show', manga.mal_id)">    
                        <img :src="manga.images?.jpg?.image_url" :alt="manga.title" class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105">
                    </Link>
                    
                    <div v-if="manga.score" class="absolute top-2 right-2 bg-black/70 backdrop-blur-sm text-xs px-2 py-1 rounded font-bold text-yellow-400">
                        ⭐ {{ manga.score }}
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

                <div class="p-4">
                    <h2 class="text-sm font-bold text-gray-100 truncate mb-1" :title="manga.title">{{ manga.title }}</h2>
                    <div class="flex justify-between items-center text-xs text-gray-400 mt-2">
                        <span>{{ manga.chapters ? manga.chapters + ' ch.' : 'En cours' }}</span>
                        <span>{{ manga.status }}</span>
                    </div>
                </div>
                
            </div>
        </div>

    </div>
    </AppLayout>
</template>