<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { Crown, MessageCircle } from 'lucide-vue-next';
import * as LucideIcons from 'lucide-vue-next';

import RadarChart from '@/components/RadarChart.vue';

const props = defineProps({
    animes: Array,
    targetUser: Object,
    radarData: Object
});

const getIconComponent = (iconName) => {
    if (!iconName) return LucideIcons.HelpCircle

    const pascalName = iconName
        .split('-')
        .map(word => word.charAt(0).toUpperCase() + word.slice(1))
        .join('');

    return LucideIcons[pascalName] || LucideIcons.HelpCircle;
};

const searchQuery = ref('');
const currentTab = ref('all');
const tabs = [
    { key: 'all', label: 'Tout' },
    { key: 'watching', label: 'En cours' },
    { key: 'completed', label: 'Terminés' },
    { key: 'plan_to_watch', label: 'À voir' },
    { key: 'dropped', label: 'Abandonnés' }
];

const goBack = () => {
    if (window.history.length > 1) {
        window.history.back();
    } else {
        router.visit(route('library'));
    }
};

const statusLabel = (status) => {
    const map = { watching: 'En cours', completed: 'Fini', plan_to_watch: 'À voir', dropped: 'Stop' };
    return map[status] || status;
};

const statusClasses = (status) => {
    const map = {
        watching: 'bg-blue-100 text-blue-700',
        completed: 'bg-green-100 text-green-700',
        plan_to_watch: 'bg-gray-100 text-gray-700',
        dropped: 'bg-red-100 text-red-700'
    };
    return map[status] || 'bg-gray-100 text-gray-500';
};

const filteredAnimes = computed(() => {
    let result = currentTab.value === 'all'
        ? props.animes
        : props.animes.filter(anime => anime.pivot.status === currentTab.value);

    if (searchQuery.value) {
        const lowerQuery = searchQuery.value.toLowerCase();
        result = result.filter(anime =>
            anime.title.toLowerCase().includes(lowerQuery) ||
            (anime.genres && anime.genres.some(g => g.name.toLowerCase().includes(lowerQuery)))
        );
    }
    return result;
});
</script>

<template>

    <Head :title="`Liste de ${targetUser.name}`" />

    <AppLayout>
        <div
            class="bg-gradient-to-r from-blue-600 to-violet-600 text-white pb-20 pt-10 px-4 sm:px-6 lg:px-8 -mt-6 mb-6 shadow-lg">
            <div class="max-w-7xl mx-auto flex flex-col md:flex-row justify-between items-center gap-6">
                <div class="flex items-center gap-4">
                    <div
                        class="h-16 w-16 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center shadow-inner text-2xl font-bold">
                        {{ targetUser.name.charAt(0).toUpperCase() }}
                    </div>
                    <div>
                        <h2 class="text-3xl font-bold tracking-tight flex items-center gap-2 leading-none">
                            {{ targetUser.name }}
                        </h2>
                        <p class="text-blue-100 mt-1 text-sm font-medium opacity-90">{{ animes.length }} entrées</p>
                    </div>
                </div>

                <button @click="goBack"
                    class="group flex items-center gap-2 px-5 py-2.5 bg-white/10 hover:bg-white/20 backdrop-blur-md rounded-full text-sm font-semibold text-white transition-all duration-300 border border-white/10 hover:border-white/30 hover:shadow-lg hover:-translate-y-0.5 cursor-pointer">
                    <span>Retour</span>
                </button>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-16 relative z-10 pb-12">

            <div
                class="bg-white rounded-2xl shadow-md p-4 mb-6 flex flex-col md:flex-row justify-between items-center gap-4 border border-gray-100">
                <div
                    class="flex space-x-1 overflow-x-auto no-scrollbar w-full md:w-auto bg-gray-100/50 p-1 rounded-full">
                    <button v-for="tab in tabs" :key="tab.key" @click="currentTab = tab.key" :class="['px-5 py-2 rounded-full font-bold text-xs uppercase tracking-wider transition-all duration-300',
                        currentTab === tab.key
                            ? 'bg-white text-blue-600 shadow-sm scale-[1.02]'
                            : 'text-gray-500 hover:text-gray-700 hover:bg-gray-200/50']">
                        {{ tab.label }}
                    </button>
                </div>

                <div class="relative w-full md:w-80 group">
                    <input v-model="searchQuery" type="text" placeholder="Rechercher un titre ou un genre..."
                        class="pl-4 block w-full rounded-full border-0 bg-gray-100 py-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300/50 placeholder:text-gray-500 focus:ring-2 focus:ring-inset focus:ring-blue-500 sm:text-sm sm:leading-6 transition-all hover:bg-gray-50">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8"
                v-if="(targetUser.badges && targetUser.badges.length > 0) || (radarData && radarData.labels.length > 0)">

                <div v-if="targetUser.badges && targetUser.badges.length > 0"
                    class="bg-white rounded-2xl shadow-md p-5 border border-gray-100"
                    :class="!(radarData && radarData.labels.length > 0) ? 'md:col-span-2' : ''">
                    <h3 class="text-xs font-extrabold text-gray-400 uppercase tracking-widest mb-4">Badges</h3>
                    <div class="flex flex-wrap gap-4">
                        <div v-for="badge in targetUser.badges" :key="badge.id"
                            class="group relative flex flex-col items-center">
                            <div class="w-12 h-12 rounded-full flex items-center justify-center shadow-lg transition-all duration-300 group-hover:scale-110 cursor-help bg-gray-900 border-2"
                                :style="{
                                    borderColor: badge.color || '#4B5563',
                                    color: badge.color || '#9CA3AF',
                                    boxShadow: `0 0 10px ${badge.color || '#4B5563'}50`
                                }">
                                <component :is="getIconComponent(badge.icon)" class="w-6 h-6 drop-shadow-md"
                                    :stroke-width="2.5" />
                            </div>

                            <div
                                class="absolute bottom-full mb-3 hidden group-hover:block w-56 bg-gray-900 text-white text-xs p-3 rounded-xl shadow-2xl z-50 text-center pointer-events-none">
                                <p
                                    class="font-extrabold text-sm mb-1 text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-violet-400">
                                    {{ badge.name }}</p>
                                <p class="opacity-90">{{ badge.description }}</p>
                                <div
                                    class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-gray-900">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="radarData && radarData.labels.length > 0"
                    class="bg-white rounded-2xl shadow-md p-5 border border-gray-100 flex flex-col"
                    :class="!(targetUser.badges && targetUser.badges.length > 0) ? 'md:col-span-2' : ''">
                    <h3 class="text-xs font-extrabold text-gray-400 uppercase tracking-widest mb-2">Top
                        Genres</h3>
                    <div class="flex-grow w-full relative min-h-[200px]">
                        <RadarChart :labels="radarData.labels" :values="radarData.values" />
                    </div>
                </div>

            </div>

            <div v-if="filteredAnimes.length > 0" class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-6">
                <Link v-for="anime in filteredAnimes" :key="anime.id" :href="route('animes.show', anime.mal_id)"
                    class="group relative rounded-2xl overflow-hidden transition-all duration-300 cursor-pointer flex flex-col h-full"
                    :class="anime.pivot.is_stu
                        ? 'bg-gray-50 ring-4 ring-yellow-500 shadow-2xl shadow-yellow-500/20 scale-[1.02] z-10'
                        : 'bg-white shadow-sm hover:shadow-xl hover:-translate-y-1 border border-gray-100/50'">
                    <div class="aspect-[2/3] overflow-hidden relative bg-gray-200">
                        <img :src="anime.image_url" :alt="anime.title"
                            class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">

                        <div
                            class="absolute inset-0 bg-black/80 flex flex-col justify-center p-4 opacity-0 group-hover:opacity-100 transition-opacity duration-300 z-30">
                            <div v-if="anime.pivot.review" class="max-h-full overflow-y-auto no-scrollbar">
                                <div class="flex items-center gap-2 mb-2 text-blue-400">
                                    <MessageCircle class="w-5 h-5" />
                                    <span class="font-bold text-sm">Avis de {{ targetUser.name }} :</span>
                                </div>
                                <p class="text-white text-xs italic leading-relaxed">
                                    "{{ anime.pivot.review }}"
                                </p>
                            </div>
                        </div>

                        <div v-if="anime.pivot.is_stu"
                            class="absolute top-0 left-0 w-full z-20 bg-gradient-to-b from-black/90 via-black/60 to-transparent pt-3 pb-6">
                            <div
                                class="flex items-center gap-2 justify-center text-yellow-400 font-black tracking-widest text-xs uppercase drop-shadow-[0_2px_2px_rgba(0,0,0,0.8)]">
                                <Crown class="w-4 h-4 fill-current animate-pulse" /> S.T.U.
                            </div>
                        </div>

                        <div class="absolute top-3 right-3 z-40 flex items-center gap-2">
                            <div v-if="anime.pivot.review"
                                class="flex items-center justify-center bg-white/95 backdrop-blur-md w-7 h-7 rounded-full shadow-sm text-blue-500">
                                <MessageCircle class="w-4 h-4" />
                            </div>

                            <div v-if="anime.pivot.score > 0"
                                class="flex items-center gap-1 bg-white/95 backdrop-blur-md px-2.5 py-1 rounded-full shadow-sm">
                                <span class="text-yellow-500 text-sm">★</span>
                                <span class="text-xs font-extrabold text-gray-800">{{ anime.pivot.score }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="p-4 flex flex-col flex-grow justify-between relative z-10"
                        :class="anime.pivot.is_stu ? 'bg-amber-50/10' : 'bg-white'">
                        <div>
                            <div
                                class="flex flex-wrap gap-1.5 mb-3 h-6 overflow-hidden content-start opacity-80 group-hover:opacity-100 transition-opacity">
                                <span v-for="g in anime.genres?.slice(0, 3)" :key="g.id"
                                    class="text-[10px] font-bold text-gray-600 bg-gray-100 px-2 py-0.5 rounded-md">
                                    {{ g.name }}
                                </span>
                            </div>

                            <h3 class="font-bold leading-tight line-clamp-2 mb-2 transition-colors"
                                :class="anime.pivot.is_stu ? 'text-yellow-600' : 'text-gray-900 group-hover:text-blue-600'"
                                :title="anime.title">
                                {{ anime.title }}
                            </h3>
                        </div>

                        <div class="flex justify-between items-center mt-4 pt-3 border-t"
                            :class="anime.pivot.is_stu ? 'border-yellow-200' : 'border-gray-50/80'">
                            <span v-if="anime.pivot.is_stu"
                                class="text-[10px] px-2.5 py-1 rounded-full font-extrabold uppercase tracking-wider bg-gradient-to-r from-yellow-400 to-amber-500 text-white shadow-sm">
                                Chef d'Oeuvre
                            </span>
                            <span v-else
                                :class="['text-[10px] px-2.5 py-1 rounded-full font-extrabold uppercase tracking-wider', statusClasses(anime.pivot.status)]">
                                {{ statusLabel(anime.pivot.status) }}
                            </span>

                            <span class="text-xs font-medium font-mono px-2 py-1 rounded-md"
                                :class="anime.pivot.is_stu ? 'bg-yellow-100 text-yellow-700' : 'text-gray-500 bg-gray-50'">
                                Ep. {{ anime.pivot.progress }}
                            </span>
                        </div>
                    </div>
                </Link>
            </div>

            <div v-else
                class="flex flex-col items-center justify-center py-20 text-center bg-white rounded-3xl border border-dashed border-gray-300/70">
                <h3 class="text-xl font-bold text-gray-900 mb-2">Aucun résultat trouvé</h3>
                <p class="text-gray-500 max-w-md mx-auto">
                    Essayez de modifier vos filtres ou votre recherche.
                </p>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar {
    display: none;
}

.no-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>