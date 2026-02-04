<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Search as SearchIcon, TrendingUp, Plus, Check } from 'lucide-vue-next';
import { useToast } from 'vue-toastification';

const props = defineProps({
    animes: Array,
    filters: Object,
});

const toast = useToast();
const query = ref(props.filters?.search || '');
const isSearching = ref(false);
const addedAnimes = ref(new Set());

let timeout = null;

const handleSearch = () => {
    isSearching.value = true;
    clearTimeout(timeout);
    timeout = setTimeout(() => {
        router.get(route('anime.search'), { search: query.value }, {
            preserveState: true,
            preserveScroll: true,
            onFinish: () => isSearching.value = false
        });
    }, 500);
};

const quickAdd = (anime) => {
    const form = useForm({
        mal_id: anime.mal_id,
        title: anime.title,
        image_url: anime.images.jpg.large_image_url,
        episodes: anime.episodes || null,
    });

    form.post(route('animes.store'), {
        preserveScroll: true,
        onSuccess: () => {
            addedAnimes.value.add(anime.mal_id);
            toast.success(`${anime.title} ajouté !`);
        },
        onError: () => toast.error("Erreur lors de l'ajout.")
    });
};

const searchTag = (tag) => {
    query.value = tag;
    handleSearch();
};
</script>

<template>

    <Head title="Recherche" />
    <AppLayout>
        <div class="min-h-screen bg-gray-50/50 pb-20">
            <div class="bg-white shadow-sm border-b border-gray-100 pt-12 pb-10 px-4 sm:px-6 lg:px-8">
                <div class="max-w-3xl mx-auto text-center space-y-6">
                    <h1 class="text-3xl font-black text-gray-900 tracking-tight">
                        Trouvez votre prochaine animé
                    </h1>
                    <div class="relative group">
                        <div
                            class="absolute -inset-1 bg-gradient-to-r from-blue-600 to-violet-600 rounded-full blur opacity-25 group-hover:opacity-50 transition duration-1000">
                        </div>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-6 flex items-center pointer-events-none">
                                <SearchIcon
                                    class="h-6 w-6 text-gray-400 group-focus-within:text-blue-600 transition-colors" />
                            </div>
                            <input v-model="query" @input="handleSearch" type="text"
                                class="block w-full pl-14 pr-12 py-4 border-0 rounded-full leading-5 bg-white text-gray-900 placeholder-gray-400 focus:ring-0 shadow-xl text-lg font-medium transition-all"
                                placeholder="Rechercher un titre..." autofocus>
                            <div v-if="isSearching" class="absolute inset-y-0 right-0 pr-6 flex items-center">
                                <svg class="animate-spin h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg"
                                    fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                        stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-10">
                <div v-if="animes && animes.length > 0">
                    <h2 class="font-bold text-gray-500 mb-6 uppercase tracking-wide text-sm flex items-center gap-2">
                        <TrendingUp class="w-4 h-4" /> Résultats ({{ animes.length }})
                    </h2>

                    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-x-6 gap-y-10">
                        <div v-for="anime in animes" :key="anime.mal_id" class="group relative flex flex-col gap-3">

                            <div
                                class="relative aspect-[2/3] rounded-2xl overflow-hidden bg-gray-200 shadow-md transition-all duration-300 group-hover:shadow-2xl group-hover:-translate-y-2">
                                <Link :href="route('animes.show', anime.mal_id)" class="block w-full h-full">
                                    <img :src="anime.images.jpg.large_image_url" :alt="anime.title"
                                        class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
                                </Link>

                                <div v-if="anime.score"
                                    class="absolute top-3 right-3 bg-black/60 backdrop-blur-md text-white font-bold px-2 py-1 rounded-lg text-xs border border-white/10 flex items-center gap-1">
                                    <span class="text-yellow-400">★</span> {{ anime.score }}
                                </div>

                                <button @click.stop.prevent="quickAdd(anime)" :disabled="addedAnimes.has(anime.mal_id)"
                                    class="absolute bottom-3 right-3 p-3 rounded-full shadow-lg border border-white/10 transition-all duration-300 transform translate-y-12 opacity-0 group-hover:translate-y-0 group-hover:opacity-100 focus:outline-none z-20"
                                    :class="addedAnimes.has(anime.mal_id) ? 'bg-green-500 text-white cursor-default' : 'bg-blue-600 text-white hover:bg-blue-700 hover:scale-110 cursor-pointer'">
                                    <Check v-if="addedAnimes.has(anime.mal_id)" class="w-5 h-5" />
                                    <Plus v-else class="w-5 h-5" />
                                </button>
                            </div>

                            <Link :href="route('animes.show', anime.mal_id)">
                                <h3
                                    class="font-bold text-gray-900 leading-tight line-clamp-2 group-hover:text-blue-600 transition-colors">
                                    {{ anime.title }}
                                </h3>
                                <p class="text-xs text-gray-400 mt-1 line-clamp-1">
                                    {{ anime.year || '?' }} • {{ anime.type }}
                                </p>
                            </Link>
                        </div>
                    </div>
                </div>

                <div v-else-if="query && !isSearching" class="text-center py-24">
                    <h3 class="text-xl font-bold text-gray-900">Aucun résultat</h3>
                </div>
                <div v-else-if="!query"
                    class="flex flex-col items-center justify-center py-20 opacity-40 select-none pointer-events-none">
                    <SearchIcon class="w-32 h-32 text-gray-300 mb-4" />
                    <p class="text-xl font-bold text-gray-300">Rechercher un animé</p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(10px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fade-in-up {
    animation: fadeInUp 0.5s ease-out forwards;
}
</style>