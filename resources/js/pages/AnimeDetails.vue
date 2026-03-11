<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm, router, usePage } from '@inertiajs/vue3';
import { ref, onMounted, computed, watch } from 'vue';
import axios from 'axios';
import { useToast } from 'vue-toastification';
import { Crown, Sparkles } from 'lucide-vue-next';

import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';

const props = defineProps({
    anime: Object
});

const toast = useToast();
const jikanData = ref(null);
const loading = ref(true);
const isEditing = ref(false);

const availableImages = ref([]);
const isLoadingImages = ref(false);
const showGallery = ref(false);

const recommendations = ref([]);
const loadingRecommendations = ref(false);
const showRecommendations = ref(false);

const fetchRecommendations = async () => {
    if (recommendations.value.length > 0) {
        showRecommendations.value = !showRecommendations.value;
        return;
    }

    showRecommendations.value = true;
    loadingRecommendations.value = true;

    try {
        const response = await axios.get(`https://api.jikan.moe/v4/anime/${props.anime.mal_id}/recommendations`);
        recommendations.value = response.data.data.slice(0, 12);
    } catch (error) {
        console.error("Erreur lors du chargement des recommandations", error);
        toast.error("Impossible de charger les recommandations.");
    } finally {
        loadingRecommendations.value = false;
    }
};

const myData = computed(() => {
    return props.anime.users?.length > 0 ? props.anime.users[0].pivot : null;
});


const goBack = () => {
    if (window.history.length > 1) {
        window.history.back();
    } else {
        router.visit(route('library'));
    }
};

const form = useForm({
    mal_id: props.anime.mal_id,
    title: props.anime.title,
    image_url: props.anime.image_url,
    episodes: props.anime.episodes,

    status: myData.value?.status || 'plan_to_watch',
    score: myData.value?.score?.toString() || '0',
    progress: myData.value?.progress ?? (myData.value?.status === 'completed' ? (props.anime.episodes || 0) : 0),
    review: myData.value?.review || '',
});

const saveChanges = () => {
    form.transform((data) => ({
        ...data,
        _method: 'PUT'
    })).post(route('animes.update', props.anime.id), {
        onSuccess: () => {
            isEditing.value = false;
            toast.success("Dossier mis à jour !");
        },
        onError: () => toast.error("Erreur lors de la sauvegarde.")
    });
};

watch(() => props.anime, (newAnime) => {
    form.mal_id = newAnime.mal_id;
    form.title = newAnime.title;
    form.image_url = newAnime.image_url;
    form.episodes = newAnime.episodes;

    const myPivot = newAnime.users?.length > 0 ? newAnime.users[0].pivot : null;

    if (myPivot) {
        form.status = myPivot.status;
        form.score = myPivot.score?.toString() || '0';
        form.review = myPivot.review || '';

        if (myPivot.progress !== null) {
            form.progress = myPivot.progress;
        } else if (myPivot.status === 'completed') {
            form.progress = newAnime.episodes || 0;
        } else {
            form.progress = 0;
        }
    }
}, { immediate: true, deep: true });

onMounted(async () => {
    if (props.anime.mal_id) {
        try {
            const response = await axios.get(`https://api.jikan.moe/v4/anime/${props.anime.mal_id}`);
            jikanData.value = response.data.data;
        } catch (error) {
            console.error("Erreur Jikan", error);
        } finally {
            loading.value = false;
        }
    } else {
        loading.value = false;
    }
});

const setPantheonRank = (rank) => {
    router.post(route('animes.pantheon', props.anime.id), {
        rank: rank
    }, {
        preserveScroll: true,
        onSuccess: () => {
            if (rank === null) {
                toast.warning("Retiré du Panthéon.");
            } else {
                toast.success(`Classé #${rank} au Panthéon !`);
            }
        },
        onError: () => toast.error("Erreur lors de la mise à jour du Panthéon.")
    });
};

const toggleStu = () => {
    router.post(route('animes.toggleStu', props.anime.mal_id), {}, {
        preserveScroll: true,
        onSuccess: () => {
            const page = usePage();

            const message = page.props.flash?.success;

            if (message) {
                if (message.includes('Parfait')) {
                    toast.success(message);
                } else {
                    toast.info(message);
                }
            }
        }
    });
};
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

            <div class="relative group rounded-xl">

                <div v-if="myData?.is_stu"
                    class="absolute -inset-2 bg-gradient-to-r from-yellow-600 via-red-600 to-yellow-600 rounded-xl blur opacity-75 transition duration-1000 animate-gradient-xy">
                </div>

                <div
                    class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl overflow-hidden flex flex-col md:flex-row">

                    <div
                        class="md:w-1/3 lg:w-1/4 shrink-0 md:min-w-[300px] bg-gray-50 dark:bg-gray-900 p-6 flex flex-col items-center border-r border-gray-100 dark:border-gray-700">
                        <button @click="goBack"
                            class="group flex items-center gap-2 px-5 py-2.5 bg-white/10 hover:bg-white/20 backdrop-blur-md rounded-full text-sm font-semibold text-white transition-all duration-300 border border-white/10 hover:border-white/30 hover:shadow-lg hover:-translate-y-0.5 cursor-pointer">
                            <span>Retour</span>
                        </button>

                        <img :src="anime.image_url"
                            class="w-48 rounded-lg shadow-lg mb-6 border-4 border-white dark:border-gray-700">

                        <div
                            class="w-full bg-white dark:bg-gray-800 p-4 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">

                            <div class="flex justify-between items-center mb-4 border-b pb-2 dark:border-gray-700">
                                <h3 class="font-bold text-gray-700 dark:text-gray-200">Mon Dossier</h3>
                                <button v-if="myData || isEditing" @click="isEditing = !isEditing"
                                    class="text-xs text-blue-600 hover:underline font-semibold cursor-pointer">
                                    {{ isEditing ? 'Annuler' : 'Modifier' }}
                                </button>
                            </div>

                            <button v-if="anime.is_saved || anime.id" @click="toggleStu"
                                class="group relative flex items-center justify-center gap-3 px-6 py-3 rounded-xl font-black uppercase tracking-widest transition-all duration-300 overflow-hidden"
                                :class="anime.pivot?.is_stu
                                    ? 'bg-gradient-to-r from-amber-400 via-orange-500 to-red-600 text-white shadow-lg shadow-orange-500/50 scale-105 border border-yellow-200'
                                    : 'bg-white dark:bg-gray-800 text-gray-500 border border-gray-200 dark:border-gray-700 hover:border-amber-500 hover:text-amber-500 hover:shadow-md'">
                                <div v-if="!anime.pivot?.is_stu"
                                    class="absolute inset-0 bg-gradient-to-r from-transparent via-amber-100/20 to-transparent translate-x-[-100%] group-hover:translate-x-[100%] transition-transform duration-1000">
                                </div>

                                <div class="relative">
                                    <Crown class="w-6 h-6 transition-transform duration-300" :class="anime.pivot?.is_stu
                                        ? 'fill-current text-yellow-100 animate-pulse'
                                        : 'group-hover:scale-110'" :stroke-width="anime.pivot?.is_stu ? 1.5 : 2" />

                                    <Sparkles v-if="anime.pivot?.is_stu"
                                        class="absolute -top-3 -right-3 w-4 h-4 text-yellow-200 animate-spin-slow" />
                                </div>

                                <span class="relative z-10 text-sm md:text-base">
                                    {{ anime.pivot?.is_stu ? 'S.T.U. ACTIF' : 'ÉLIRE S.T.U.' }}
                                </span>
                            </button>

                            <div v-if="!isEditing && myData" class="space-y-3 py-3">
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-500 dark:text-gray-400">Statut</span>
                                    <span
                                        class="font-medium capitalize px-2 py-0.5 rounded-full bg-gray-100 dark:bg-gray-700 text-xs text-gray-800 dark:text-gray-200">
                                        {{ myData.status.replace(/_/g, ' ') }}
                                    </span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-500 dark:text-gray-400">Score</span>
                                    <span class="font-bold text-yellow-500">★ {{ myData.score }}/10</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-500 dark:text-gray-400">Avancement</span>
                                    <span class="font-medium text-gray-800 dark:text-gray-200">{{ myData.progress }} /
                                        {{
                                            anime.episodes || '?' }} eps</span>
                                </div>

                                <div class="w-full bg-gray-200 rounded-full h-1.5 dark:bg-gray-700">
                                    <div class="bg-blue-500 h-1.5 rounded-full"
                                        :style="{ width: (anime.episodes && anime.episodes > 0) ? Math.min((myData.progress / anime.episodes * 100), 100) + '%' : '0%' }">
                                    </div>
                                </div>

                                <div class="mt-4 pt-4 border-t border-dashed border-gray-200 dark:border-gray-700">
                                    <span
                                        class="text-xs font-bold text-gray-400 uppercase tracking-wide flex items-center gap-1 mb-3">
                                        Épingler au Panthéon
                                    </span>

                                    <div class="flex flex-wrap gap-2">
                                        <button @click="setPantheonRank(1)"
                                            :class="myData.pantheon_rank === 1 ? 'bg-yellow-500 text-white ring-2 ring-yellow-300' : 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-yellow-100 dark:hover:bg-yellow-900/30'"
                                            class="px-3 py-1.5 rounded-lg font-bold text-xs transition-all duration-200 flex-1 text-center">
                                            #1
                                        </button>

                                        <button @click="setPantheonRank(2)"
                                            :class="myData.pantheon_rank === 2 ? 'bg-gray-400 text-white ring-2 ring-gray-300' : 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700'"
                                            class="px-3 py-1.5 rounded-lg font-bold text-xs transition-all duration-200 flex-1 text-center">
                                            #2
                                        </button>

                                        <button @click="setPantheonRank(3)"
                                            :class="myData.pantheon_rank === 3 ? 'bg-amber-600 text-white ring-2 ring-amber-300' : 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-amber-100 dark:hover:bg-amber-900/30'"
                                            class="px-3 py-1.5 rounded-lg font-bold text-xs transition-all duration-200 flex-1 text-center">
                                            #3
                                        </button>

                                        <button v-if="myData.pantheon_rank" @click="setPantheonRank(null)"
                                            class="px-3 py-1.5 rounded-lg font-bold text-xs bg-red-50 text-red-600 hover:bg-red-100 dark:bg-red-900/20 dark:hover:bg-red-900/40 transition-all duration-200 ml-auto">
                                            Retirer
                                        </button>
                                    </div>
                                </div>

                                <div v-if="myData.review"
                                    class="mt-4 pt-4 border-t border-dashed border-gray-200 dark:border-gray-700">
                                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wide">Ma
                                        Critique</span>
                                    <p class="text-sm text-gray-600 dark:text-gray-300 italic mt-1 whitespace-pre-wrap">
                                        "{{
                                            myData.review }}"</p>
                                </div>
                            </div>

                            <div v-else-if="isEditing || !myData" class="space-y-4">

                                <div v-if="!isEditing && !myData" class="text-center py-4">
                                    <form
                                        @submit.prevent="form.post(route('animes.store'), { onSuccess: () => toast.success('Ajouté à la liste !') })">
                                        <Button type="submit" class="w-full" :disabled="form.processing">
                                            Ajouter à ma liste
                                        </Button>
                                    </form>
                                </div>

                                <div v-else class="space-y-4">
                                    <div class="space-y-1">
                                        <Label class="text-xs">Statut</Label>
                                        <select v-model="form.status"
                                            class="w-full text-sm rounded-md border-gray-300 p-2 bg-gray-50 dark:bg-gray-900 dark:text-white">
                                            <option value="watching">En cours</option>
                                            <option value="completed">Terminé</option>
                                            <option value="plan_to_watch">À voir</option>
                                            <option value="dropped">Abandonné</option>
                                        </select>
                                    </div>

                                    <div class="space-y-1">
                                        <Label class="text-xs">Note (/10)</Label>
                                        <select v-model="form.score"
                                            class="w-full text-sm rounded-md border-gray-300 p-2 bg-gray-50 dark:bg-gray-900 dark:text-white">
                                            <option value="0">-</option>
                                            <option v-for="n in 10" :key="n" :value="n">{{ n }}</option>
                                        </select>
                                    </div>

                                    <div class="space-y-1">
                                        <div class="flex justify-between">
                                            <Label class="text-xs">Épisodes vus</Label>
                                            <span class="text-xs text-gray-400">{{ form.progress }} / {{ anime.episodes
                                                ||
                                                '?' }}</span>
                                        </div>
                                        <Input type="number" v-model="form.progress" min="0"
                                            :max="anime.episodes || 999" class="h-9 bg-gray-50 dark:bg-gray-900" />
                                    </div>

                                    <div class="space-y-1">
                                        <Label class="text-xs">Ta Critique</Label>
                                        <textarea v-model="form.review" rows="4"
                                            class="w-full text-sm rounded-md border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 p-2 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"
                                            placeholder="Pourquoi cette note ?"></textarea>
                                    </div>

                                    <Button @click="saveChanges" class="w-full" :disabled="form.processing">
                                        {{ form.processing ? 'Sauvegarde...' : 'Enregistrer' }}
                                    </Button>
                                </div>
                            </div>
                        </div>
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

                            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">Synopsis</h2>
                            <p
                                class="text-gray-600 dark:text-gray-300 leading-relaxed mb-6 whitespace-pre-line text-justify">
                                {{ jikanData.synopsis }}
                            </p>

                            <div v-if="jikanData.trailer?.embed_url" class="mt-8">
                                <h3 class="font-bold text-gray-800 dark:text-gray-200 mb-3">Trailer</h3>
                                <iframe class="w-full aspect-video rounded-lg shadow-md"
                                    :src="jikanData.trailer.embed_url" allowfullscreen></iframe>
                            </div>

                            <div class="mt-12 border-t border-gray-100 dark:border-gray-700 pt-8">
                                <div class="flex justify-between items-center mb-6">
                                    <h3 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                        Si tu as aimé {{ anime.title_english || anime.title }}
                                    </h3>

                                    <Button @click="fetchRecommendations" variant="outline" size="sm" class="gap-2">
                                        <span v-if="showRecommendations && recommendations.length > 0">Masquer</span>
                                        <span v-else>Voir les recommandations</span>
                                    </Button>
                                </div>

                                <div v-if="showRecommendations">
                                    <div v-if="loadingRecommendations"
                                        class="grid grid-cols-3 sm:grid-cols-4 gap-4 animate-pulse">
                                        <div v-for="n in 8" :key="n" class="space-y-2">
                                            <div class="aspect-[2/3] bg-gray-200 dark:bg-gray-700 rounded-xl"></div>
                                            <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-3/4"></div>
                                        </div>
                                    </div>

                                    <div v-else-if="recommendations.length > 0"
                                        class="grid grid-cols-3 sm:grid-cols-4 gap-4">
                                        <Link v-for="rec in recommendations" :key="rec.entry.mal_id"
                                            :href="route('animes.show', rec.entry.mal_id)"
                                            class="group relative flex flex-col gap-2 cursor-pointer">
                                            <div
                                                class="relative aspect-[2/3] rounded-xl overflow-hidden bg-gray-200 shadow-md transition-all duration-300 group-hover:shadow-xl group-hover:-translate-y-1">
                                                <img :src="rec.entry.images.jpg.large_image_url" :alt="rec.entry.title"
                                                    class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">

                                                <div
                                                    class="absolute top-2 right-2 bg-black/60 backdrop-blur-md text-white text-[10px] font-bold px-1.5 py-0.5 rounded flex items-center gap-1">
                                                    Votes: {{ rec.votes }}
                                                </div>
                                            </div>
                                            <h4
                                                class="font-bold text-gray-900 dark:text-white text-xs leading-tight line-clamp-2 group-hover:text-blue-500 transition-colors">
                                                {{ rec.entry.title }}
                                            </h4>
                                        </Link>
                                    </div>

                                    <div v-else class="text-center text-gray-500 py-8">
                                        Aucune recommandation trouvée. Tu as des goûts uniques !
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>