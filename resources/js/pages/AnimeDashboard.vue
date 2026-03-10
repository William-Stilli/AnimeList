<script setup lang="ts">
import { Head, usePage, Link, router } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Play, Trophy, Clock, Tv, Star, Zap, Crown } from 'lucide-vue-next';
import { useToast } from 'vue-toastification';
import {
    Tooltip,
    TooltipContent,
    TooltipProvider,
    TooltipTrigger,
} from '@/components/ui/tooltip'

const toast = useToast();

const props = defineProps({
    watching: Array,
    stats: Object,
    badges: Array,
    stuAnime: Object,
})

const page = usePage();
const user = computed(() => page.props.auth.user);

const getCoverImage = (anime) => {
    const customPath = anime.pivot?.custom_image_path;

    if (customPath) {
        if (customPath.startsWith('http')) return customPath;
        return '/storage/' + customPath;
    }

    return anime.image_url;
};

const levelInfo = computed(() => {
    const xp = user.value.xp || 0;

    const currentLevel = Math.floor(Math.sqrt(Math.max(0, xp) / 5)) || 1;
    const nextLevel = currentLevel + 1;

    const currentLevelBaseXp = 5 * Math.pow(currentLevel, 2);
    const nextLevelReqXp = 5 * Math.pow(nextLevel, 2);

    const xpEarnedInLevel = xp - currentLevelBaseXp;
    const xpNeededForLevel = nextLevelReqXp - currentLevelBaseXp;

    const progressPercent = Math.min(100, Math.max(0, (xpEarnedInLevel / xpNeededForLevel) * 100));

    let title = 'Novice';
    let subtitle = 'Niveau 1';
    let color = 'from-emerald-400 via-green-500 to-teal-600 bg-[length:200%_auto] animate-gradient';

    if (currentLevel >= 100) {
        title = 'S.T.U.';
        subtitle = 'Souverain Transcendant Universel';
        color = 'from-gray-900 via-red-600 to-yellow-500 bg-[length:200%_auto] animate-gradient';
    }
    else if (currentLevel >= 50) {
        title = 'Divinité';
        subtitle = 'Au-dessus des lois';
        color = 'from-yellow-300 via-orange-400 to-amber-500 bg-[length:200%_auto] animate-gradient';
    }
    else if (currentLevel >= 40) {
        title = 'Sage';
        subtitle = 'L\'oracle des animés';
        color = 'from-indigo-400 via-purple-500 to-fuchsia-500 bg-[length:200%_auto] animate-gradient';
    }
    else if (currentLevel >= 25) {
        title = 'Otaku';
        subtitle = 'Membre de l\'élite';
        color = 'from-rose-500 via-red-500 to-orange-500 bg-[length:200%_auto] animate-gradient';
    }
    else if (currentLevel >= 10) {
        title = 'Weeb';
        subtitle = 'Apprenti culture';
        color = 'from-blue-400 via-cyan-500 to-sky-500 bg-[length:200%_auto] animate-gradient';
    }

    return {
        level: currentLevel,
        title: title,
        subtitle: subtitle,
        progress: progressPercent,
        currentXp: xp,
        nextXp: nextLevelReqXp,
        remaining: nextLevelReqXp - xp,
        color: color
    };
});

const incrementProgress = (anime: any) => {
    const nextProgress = Number(anime.pivot.progress) + 1;

    router.put(route('animes.update', anime.id), {
        progress: nextProgress
    }, {
        preserveScroll: true,
        onSuccess: () => {
            console.log("XP Get!");
        }
    });
};

const getProSubtitle = (type: string | undefined) => {
    const safeType = type ? type.toUpperCase() : 'UNKNOWN'

    switch (safeType) {
        case 'TV':
            return "La série d'animation au-dessus des autres";
        case 'MOVIE':
            return "Le chef-d'œuvre cinématographique au-dessus des autres";
        case 'OVA':
        case 'ONA':
        case 'SPECIAL':
            return "L'épisode spécial au-dessus des autres";
        default:
            return "L'œuvre magistrale au-dessus des autres";
    }
}
</script>

<template>

    <Head title="Dashboard" />

    <AppLayout>
        <div class="p-6 space-y-8">

            <div
                class="flex flex-col md:flex-row items-start md:items-center justify-between gap-6 bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">

                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                        {{ user.name }}
                    </h1>

                    <div class="mt-2">
                        <div class="flex items-baseline gap-2">
                            <span class="font-black text-3xl text-transparent bg-clip-text bg-gradient-to-r"
                                :class="levelInfo.color">
                                {{ levelInfo.title }}
                            </span>
                            <span
                                class="px-2 py-0.5 rounded text-xs font-bold bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 transform -translate-y-1">
                                Lvl {{ levelInfo.level }}
                            </span>
                        </div>

                        <p class="text-xs font-medium text-gray-400 uppercase tracking-widest mt-0.5">
                            {{ levelInfo.subtitle }}
                        </p>
                    </div>
                </div>

                <div class="w-full md:w-1/2 lg:w-1/3">
                    <div class="flex justify-between text-xs font-bold uppercase tracking-wider text-gray-500 mb-2">
                        <span>XP: {{ levelInfo.currentXp }}</span>
                        <span>Objectif: {{ levelInfo.nextXp }}</span>
                    </div>

                    <div
                        class="relative w-full h-6 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden shadow-inner ring-2 ring-black/5 dark:ring-white/5">
                        <div class="h-full transition-all duration-1000 ease-out flex items-center justify-end px-2"
                            :class="`bg-gradient-to-r ${levelInfo.color}`" :style="{ width: levelInfo.progress + '%' }">
                            <div class="w-full h-[1px] bg-white/30 absolute top-0 left-0"></div>
                        </div>
                    </div>

                    <p class="text-xs text-right mt-1.5 text-gray-400 italic">
                        Encore {{ levelInfo.remaining }} XP pour le niveau {{ levelInfo.level + 1 }} !
                    </p>
                </div>
            </div>

            <div v-if="badges!.length > 0"
                class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
                <h2 class="text-lg font-bold mb-4 flex items-center gap-2 text-gray-900 dark:text-white">
                    Mes Badges
                </h2>

                <div class="flex flex-wrap gap-4">
                    <TooltipProvider v-for="badge in badges" :key="badge.id">
                        <Tooltip>
                            <TooltipTrigger>
                                <div
                                    class="flex flex-col items-center justify-center p-3 w-24 h-28 bg-gray-50 dark:bg-gray-900 rounded-xl border-2 border-transparent hover:border-indigo-500 hover:scale-105 transition-all group">
                                    <span
                                        class="text-xs font-bold text-center leading-tight text-gray-700 dark:text-gray-300">
                                        {{ badge.name }}
                                    </span>
                                </div>
                            </TooltipTrigger>
                            <TooltipContent>
                                <div class="text-center">
                                    <p class="font-bold">{{ badge.name }}</p>
                                    <p class="text-xs text-gray-500">{{ badge.description }}</p>
                                    <p class="text-[10px] text-indigo-400 mt-1">+{{ badge.xp_bonus }} XP</p>
                                </div>
                            </TooltipContent>
                        </Tooltip>
                    </TooltipProvider>
                </div>
            </div>

            <div v-if="stuAnime" class="mb-8 relative group">
                <div
                    class="absolute -inset-1 bg-gradient-to-r from-yellow-600 via-red-600 to-yellow-600 rounded-2xl blur opacity-75 group-hover:opacity-100 transition duration-1000 animate-gradient-xy">
                </div>

                <div class="relative bg-gray-900 rounded-xl p-6 flex items-center gap-6 border border-yellow-500/30">
                    <div class="relative shrink-0">
                        <span class="absolute -top-6 -left-4 text-6xl z-10 filter drop-shadow-lg transform -rotate-12">
                            <Crown></Crown>
                        </span>
                        <img :src="getCoverImage(stuAnime)"
                            class="w-24 h-36 object-cover rounded-lg shadow-2xl border-2 border-yellow-500">
                    </div>

                    <div class="flex-1">
                        <h2 class="text-yellow-500 font-black tracking-[0.2em] text-sm uppercase mb-1">Catégorie S.T.U.
                        </h2>
                        <h1 class="text-3xl font-bold text-white mb-2">{{ stuAnime.title }}</h1>
                        <p class="text-gray-400 italic text-sm">
                            "{{ getProSubtitle(stuAnime.type) }}"
                        </p>
                    </div>

                    <Link :href="route('animes.show', stuAnime.mal_id)"
                        class="px-6 py-3 bg-yellow-500 hover:bg-yellow-400 text-black font-bold rounded-lg transition">
                        Voir le Trône
                    </Link>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <Card class="hover:shadow-md transition-shadow">
                    <CardContent class="flex items-center gap-4 p-6">
                        <div class="p-3 bg-blue-100 dark:bg-blue-900/40 rounded-full">
                            <Tv class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Épisodes vus</p>
                            <h3 class="text-2xl font-bold">{{ stats!.episodes }}</h3>
                        </div>
                    </CardContent>
                </Card>

                <Card class="hover:shadow-md transition-shadow">
                    <CardContent class="flex items-center gap-4 p-6">
                        <div class="p-3 bg-purple-100 dark:bg-purple-900/40 rounded-full">
                            <Clock class="w-6 h-6 text-purple-600 dark:text-purple-400" />
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Temps total</p>
                            <h3 class="text-2xl font-bold">{{ stats!.time_spent }}</h3>
                        </div>
                    </CardContent>
                </Card>

                <Card class="hover:shadow-md transition-shadow">
                    <CardContent class="flex items-center gap-4 p-6">
                        <div class="p-3 bg-yellow-100 dark:bg-yellow-900/40 rounded-full">
                            <Trophy class="w-6 h-6 text-yellow-600 dark:text-yellow-400" />
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Saisons/films complétés</p>
                            <h3 class="text-2xl font-bold">{{ stats!.completed_count }}</h3>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <div v-if="watching!.length > 0">
                <h2 class="text-xl font-bold mb-4 flex items-center gap-2 text-gray-800 dark:text-gray-100">
                    En cours
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div v-for="anime in watching" :key="anime.id" class="group relative">
                        <div
                            class="relative overflow-hidden rounded-xl shadow-lg transition-all duration-300 hover:scale-[1.02] hover:shadow-xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700">

                            <div class="aspect-video relative">
                                <img :src="getCoverImage(anime)"
                                    class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110" />
                                <div
                                    class="absolute bottom-2 right-2 bg-black/80 text-white text-[10px] font-bold px-2 py-0.5 rounded-md backdrop-blur-sm border border-white/10">
                                    EP {{ anime.pivot.progress }}
                                </div>
                            </div>

                            <div class="p-4">
                                <h3 class="font-bold truncate text-gray-900 dark:text-white" :title="anime.title">{{
                                    anime.title }}</h3>

                                <div
                                    class="mt-3 flex items-center justify-between text-xs text-gray-500 mb-1 font-medium">
                                    <span>Progression</span>
                                    <span>{{ anime.episodes ? Math.round((anime.pivot.progress / anime.episodes) * 100)
                                        + '%' : '??%' }}</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2 dark:bg-gray-700 overflow-hidden">
                                    <div class="bg-indigo-600 h-2 rounded-full transition-all duration-500"
                                        :style="{ width: anime.episodes ? (anime.pivot.progress / anime.episodes * 100) + '%' : '0%' }">
                                    </div>
                                </div>

                                <div class="mt-4 flex justify-end">
                                    <Button size="sm"
                                        class="w-full gap-2 font-bold shadow-md active:scale-95 transition-transform bg-gray-900 text-white hover:bg-indigo-600 dark:bg-gray-700 dark:hover:bg-indigo-500"
                                        @click="incrementProgress(anime)"
                                        :disabled="anime.episodes && anime.pivot.progress >= anime.episodes">

                                        <Zap class="w-4 h-4 text-yellow-400 fill-current"
                                            v-if="!(anime.episodes && anime.pivot.progress >= anime.episodes)" />
                                        <span v-if="anime.episodes && anime.pivot.progress >= anime.episodes">Terminé
                                            !</span>
                                        <span v-else>+1 Épisode</span>
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div v-else
                class="text-center py-16 bg-gray-50 dark:bg-gray-800/30 rounded-2xl border-2 border-dashed border-gray-300 dark:border-gray-700">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Zone Vide</h3>
                <p class="text-gray-500 mb-6">Ajoute un animé pour commencer à farmer ton XP !</p>
                <Link :href="route('library')">
                    <Button>Explorer la bibliothèque</Button>
                </Link>
            </div>

        </div>
    </AppLayout>
</template>