<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, Link } from '@inertiajs/vue3';
import { ref, computed, onMounted, watch } from 'vue';
import axios from 'axios';
import { useToast } from 'vue-toastification';
import { useAutoAnimate } from '@formkit/auto-animate/vue'
import AnimeToast from '@/components/AnimeToast.vue';
import confetti from 'canvas-confetti';
import { Crown, Pencil, Trash2, RotateCcw } from 'lucide-vue-next';

const [parent] = useAutoAnimate()
const toast = useToast();

const minScore = ref(0);
const maxScore = ref(10);

const searchQuery = ref('');
const animes = ref([]);
const currentTab = ref('all');
const tabs = [
    { key: 'all', label: 'Tout' },
    { key: 'watching', label: 'En cours' },
    { key: 'completed', label: 'Terminé' },
    { key: 'plan_to_watch', label: 'À voir' },
    { key: 'dropped', label: 'Abandonné' }
];

const isModalOpen = ref(false);
const editingAnime = ref(null);

const form = ref({
    status: '',
    progress: 0,
    score: 0,
    selected_image_url: null,
    reset_image: false,
    pantheon_rank: null
});

const galleryImages = ref([]);
const isLoadingGallery = ref(false);

watch(() => form.value.status, (newStatus) => {
    if (newStatus === 'completed' && editingAnime.value?.episodes) {
        form.value.progress = editingAnime.value.episodes;
    }
});

watch(maxScore, (newValue) => {
    if (newValue > maxScore.value) {
        minScore.value = maxScore.value;
    }
});

watch(maxScore, (newValue) => {
    if (newValue < minScore.value) {
        maxScore.value = minScore.value;
    }
});

onMounted(async () => {
    refreshLibrary();
});

const refreshLibrary = async () => {
    try {
        const response = await axios.get('/my-animes');
        animes.value = response.data;
    } catch (error) {
        console.error("Erreur chargement bibliothèque:", error);
    }
};

const getCoverImage = (anime) => {
    const customPath = anime.pivot?.custom_image_path;

    if (customPath) {
        if (customPath.startsWith('http')) return customPath;
        return '/storage/' + customPath;
    }

    return anime.image_url;
};

const filteredAnimes = computed(() => {
    let result = currentTab.value === 'all' ? animes.value : animes.value.filter(anime => anime.pivot.status === currentTab.value);

    if (searchQuery.value) {
        const lowerQuery = searchQuery.value.toLowerCase();
        result = result.filter(anime => { return anime.genres && anime.genres.some(genre => genre.name.toLowerCase().includes(lowerQuery)) });
    }

    result = result.filter(anime => {
        const score = anime.pivot.score;
        return score >= minScore.value && score <= maxScore.value;
    })

    return result
});

const openEditModal = async (anime) => {
    editingAnime.value = anime;

    form.value = {
        status: anime.pivot.status,
        progress: anime.pivot.progress,
        score: anime.pivot.score,
        selected_image_url: null,
        reset_image: false,
        pantheon_rank: anime.pivot.pantheon_rank || null
    };

    isModalOpen.value = true;

    galleryImages.value = [];
    isLoadingGallery.value = true;

    if (!anime.mal_id) {
        isLoadingGallery.value = false;
        return;
    }

    try {
        const url = `https://api.jikan.moe/v4/anime/${anime.mal_id}/pictures`;
        const response = await axios.get(url);
        galleryImages.value = response.data.data;
    } catch (error) {
        console.error("ERREUR APPEL API JIKAN :", error);
    } finally {
        isLoadingGallery.value = false;
    }
};

const closeModal = () => {
    isModalOpen.value = false;
    editingAnime.value = null;
};

const selectCover = (url) => {
    form.value.selected_image_url = url;
    form.value.reset_image = false;
    toast.info("Image sélectionnée ! Clique sur Sauvegarder pour valider.");
};

const resetCover = () => {
    form.value.reset_image = true;
    form.value.selected_image_url = null;
    toast.info("L'image sera réinitialisée à la sauvegarde.");
};

const saveChanges = async () => {
    if (!editingAnime.value) return;

    if (editingAnime.value.episodes && form.value.progress > editingAnime.value.episodes) {
        toast.warning(`${editingAnime.value.title} n'a que ${editingAnime.value.episodes} épisodes.`);
        form.value.progress = editingAnime.value.episodes;
        return;
    }

    try {
        await axios.post(`/animes/${editingAnime.value.id}`, {
            ...form.value,
            _method: 'PUT'
        });

        const index = animes.value.findIndex(a => a.id === editingAnime.value.id);
        if (index !== -1) {
            animes.value[index].pivot.status = form.value.status;
            animes.value[index].pivot.progress = form.value.progress;
            animes.value[index].pivot.score = form.value.score;

            if (form.value.pantheon_rank !== null) {
                animes.value.forEach(a => {
                    if (a.id !== editingAnime.value.id && a.pivot.pantheon_rank === form.value.pantheon_rank) {
                        a.pivot.pantheon_rank = null;
                    }
                });
            }
            animes.value[index].pivot.pantheon_rank = form.value.pantheon_rank;

            if (form.value.selected_image_url) {
                animes.value[index].pivot.custom_image_path = form.value.selected_image_url;
            } else if (form.value.reset_image) {
                animes.value[index].pivot.custom_image_path = null;
            }
        }

        if (form.value.status === 'completed') {
            confetti({ particleCount: 150, spread: 70, origin: { y: 0.6 } })
        }

        toast.success({
            component: AnimeToast,
            props: {
                title: editingAnime.value.title,
                image: getCoverImage(editingAnime.value),
                message: "Mise à jour réussie"
            }
        }, { timeout: 3000, icon: false });

        closeModal();
    } catch (error) {
        toast.error("Erreur lors de la sauvegarde !");
        console.error(error);
    }
};

const deleteAnime = async () => {
    if (!editingAnime.value) return;
    if (!confirm(`Confirmé la suppression de "${editingAnime.value.title}" de la liste`)) return;

    try {
        await axios.delete(`/animes/${editingAnime.value.id}`);
        animes.value = animes.value.filter(a => a.id !== editingAnime.value.id);
        toast.warning("Animé supprimé!")
        closeModal();
    } catch (error) {
        console.error("Impossible de supprimer :", error);
        toast.error("Erreur lors de la suppression.");
    }
};
</script>

<template>

    <Head title="Ma Bibliothèque" />

    <AppLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Ma Bibliothèque</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
                            <div class="flex space-x-2 overflow-x-auto pb-2 w-full md:w-auto">
                                <button v-for="tab in tabs" :key="tab.key" @click="currentTab = tab.key"
                                    :class="['px-4 py-2 rounded-full font-bold text-sm transition', currentTab === tab.key ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200']">
                                    {{ tab.label }}
                                </button>
                            </div>

                            <div class="flex flex-col md:flex-row gap-6 p-4 rounded-lg mb-6">

                                <div class="flex-1">
                                    <label class="block text-sm font-medium text-gray-600 mb-2">
                                        Score Min : <span class="text-blue-400 font-bold">{{ minScore }}</span>
                                    </label>
                                    <input type="range" v-model.number="minScore" min="0" max="10" step="1"
                                        class="w-full h-2 bg-gray-600 rounded-lg appearance-none cursor-pointer accent-blue-500">
                                </div>

                                <div class="flex-1">
                                    <label class="block text-sm font-medium text-gray-600 mb-2">
                                        Score Max : <span class="text-purple-400 font-bold">{{ maxScore }}</span>
                                    </label>
                                    <input type="range" v-model.number="maxScore" min="0" max="10" step="1"
                                        class="w-full h-2 bg-gray-600 rounded-lg appearance-none cursor-pointer accent-purple-500">
                                </div>

                            </div>

                            <div class="relative w-full md:w-64">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </div>
                                <input v-model="searchQuery" type="text" placeholder="Filtrer par genre..."
                                    class="pl-10 block w-full rounded-full border-gray-300 bg-gray-50 focus:border-blue-500 focus:ring-blue-500 sm:text-sm transition">
                            </div>
                        </div>

                        <div v-if="filteredAnimes.length > 0" ref="parent"
                            class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-6">

                            <Link v-for="anime in filteredAnimes" :key="anime.id"
                                :href="route('animes.show', anime.mal_id)" @contextmenu.prevent="openEditModal(anime)"
                                class="relative group rounded-xl overflow-hidden transition-all duration-300 hover:-translate-y-1 hover:shadow-xl flex flex-col cursor-pointer"
                                :class="anime.pivot.is_stu
                                    ? 'bg-gray-50 ring-4 ring-yellow-500 shadow-2xl shadow-yellow-500/20 scale-[1.02] z-10'
                                    : 'bg-white dark:bg-gray-800 shadow-sm border border-gray-100 dark:border-gray-700'"
                                title="Clic gauche: Détails | Clic droit: Modifier">

                                <div class="block relative aspect-[2/3] overflow-hidden bg-gray-200">
                                    <img :src="getCoverImage(anime)" :alt="anime.title"
                                        class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110">

                                    <div v-if="anime.pivot.is_stu"
                                        class="absolute top-0 left-0 w-full z-20 bg-gradient-to-b from-black/90 via-black/60 to-transparent pt-3 pb-6">
                                        <div
                                            class="flex items-center gap-2 justify-center text-yellow-400 font-black tracking-widest text-xs uppercase drop-shadow-[0_2px_2px_rgba(0,0,0,0.8)]">
                                            <Crown class="w-4 h-4 fill-current animate-pulse" /> S.T.U.
                                        </div>
                                    </div>

                                    <div v-if="anime.pivot.pantheon_rank"
                                        class="absolute top-2 left-2 w-8 h-8 rounded-full flex items-center justify-center font-black text-white shadow-lg border-2 z-20"
                                        :class="{
                                            'bg-yellow-500 border-yellow-300 shadow-yellow-500/50': anime.pivot.pantheon_rank === 1,
                                            'bg-gray-400 border-gray-300 shadow-gray-400/50': anime.pivot.pantheon_rank === 2,
                                            'bg-amber-600 border-amber-300 shadow-amber-600/50': anime.pivot.pantheon_rank === 3
                                        }">
                                        #{{ anime.pivot.pantheon_rank }}
                                    </div>

                                    <div v-if="anime.pivot.score > 0"
                                        class="absolute top-2 right-2 bg-black/70 backdrop-blur-md text-white font-bold px-2 py-1 rounded-lg text-xs border border-white/10 z-10">
                                        <span class="text-yellow-400">★</span> {{ anime.pivot.score }}
                                    </div>

                                    <div
                                        class="absolute inset-0 bg-black/10 opacity-0 group-hover:opacity-100 transition-opacity">
                                    </div>
                                </div>

                                <div class="p-4 flex flex-col flex-1 gap-2">
                                    <h3 class="font-bold truncate text-base"
                                        :class="anime.pivot.is_stu ? 'text-yellow-600' : 'text-gray-900 dark:text-white'">
                                        {{ anime.title }}
                                    </h3>

                                    <div class="flex flex-wrap gap-1 opacity-70">
                                        <span v-for="g in anime.genres?.slice(0, 2)" :key="g.id"
                                            class="text-[10px] bg-gray-100 dark:bg-gray-700 px-1.5 py-0.5 rounded text-gray-600 dark:text-gray-300">
                                            {{ g.name }}
                                        </span>
                                    </div>

                                    <div class="mt-auto pt-2">
                                        <div
                                            class="flex justify-between text-xs text-gray-500 dark:text-gray-400 mb-1 font-mono">
                                            <span>Ep {{ anime.pivot.progress }}</span>
                                            <span>{{ anime.episodes ? '/ ' + anime.episodes : '?' }}</span>
                                        </div>
                                        <div
                                            class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-1.5 overflow-hidden">
                                            <div class="h-full rounded-full transition-all duration-500"
                                                :class="anime.pivot.status === 'completed' ? 'bg-green-500' : (anime.pivot.is_stu ? 'bg-yellow-500' : 'bg-blue-600')"
                                                :style="{ width: anime.episodes ? (anime.pivot.progress / anime.episodes * 100) + '%' : '100%' }">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </Link>
                        </div>

                        <div v-else class="text-center py-10 text-gray-500 italic">
                            Rien à voir ici. Va ajouter des animés !
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="isModalOpen"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/80 backdrop-blur-sm">
            <div
                class="bg-white rounded-xl shadow-2xl w-full max-w-md overflow-hidden transform transition-all border border-gray-100 flex flex-col max-h-[90vh]">

                <div class="bg-gray-50 px-6 py-4 border-b border-gray-100 flex justify-between items-center shrink-0">
                    <h3 class="font-bold text-lg text-gray-800 truncate pr-4">{{ editingAnime?.title }}</h3>
                    <button @click="closeModal"
                        class="text-gray-400 hover:text-red-500 text-2xl font-bold transition">&times;</button>
                </div>

                <div class="p-6 space-y-5 overflow-y-auto custom-scrollbar">

                    <div>
                        <div class="flex justify-between items-center mb-3">
                            <h4 class="font-bold text-gray-700 flex items-center gap-2">
                                Galerie & Couverture
                                <span v-if="isLoadingGallery"
                                    class="text-xs font-normal text-gray-400 animate-pulse">(Chargement...)</span>
                            </h4>

                            <button v-if="editingAnime?.pivot?.custom_image_path || form.selected_image_url"
                                @click="resetCover" type="button"
                                class="text-xs text-red-500 flex items-center gap-1 hover:underline"
                                title="Revenir à l'image par défaut">
                                <RotateCcw class="w-3 h-3" /> Reset
                            </button>
                        </div>

                        <div v-if="form.selected_image_url"
                            class="mb-3 p-2 bg-green-50 border border-green-200 rounded-lg flex items-center gap-3">
                            <img :src="form.selected_image_url" class="h-12 w-8 object-cover rounded shadow" />
                            <div class="text-xs text-green-700 font-medium">Image sélectionnée <br>(Sauvegarde requise)
                            </div>
                        </div>
                        <div v-else-if="form.reset_image"
                            class="mb-3 p-2 bg-yellow-50 border border-yellow-200 rounded-lg text-xs text-yellow-700">
                            L'image sera réinitialisée à la sauvegarde.
                        </div>

                        <div v-if="galleryImages.length > 0"
                            class="grid grid-cols-4 gap-2 max-h-40 overflow-y-auto pr-2 custom-scrollbar">
                            <div v-for="(img, index) in galleryImages" :key="index"
                                class="relative group cursor-pointer" @click="selectCover(img.jpg.image_url)">
                                <img :src="img.jpg.image_url"
                                    class="w-full h-24 object-cover rounded-md border border-gray-200 hover:scale-105 hover:border-blue-500 hover:ring-2 hover:ring-blue-300 transition duration-300"
                                    :class="form.selected_image_url === img.jpg.image_url ? 'ring-2 ring-green-500 border-green-500' : ''">
                            </div>
                        </div>

                        <div v-else-if="!isLoadingGallery" class="text-sm text-gray-400 italic">
                            Pas d'images supplémentaires trouvées.
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Statut</label>
                        <select v-model="form.status"
                            class="w-full rounded-lg border-gray-300 bg-white text-gray-900 shadow-sm focus:border-blue-500 focus:ring-blue-500 py-2.5">
                            <option value="watching" class="text-gray-900">En cours de visionnage</option>
                            <option value="completed" class="text-gray-900">Terminé</option>
                            <option value="plan_to_watch" class="text-gray-900">À voir plus tard</option>
                            <option value="dropped" class="text-gray-900">Abandonné</option>
                        </select>
                    </div>

                    <div class="flex gap-4">
                        <div class="flex-1">
                            <label class="block text-sm font-bold text-gray-700 mb-1">Épisodes vus</label>
                            <div class="flex items-center">
                                <input type="number" v-model="form.progress" min="0"
                                    :max="editingAnime?.episodes || null"
                                    class="w-full rounded-lg border-gray-300 bg-white text-gray-900 shadow-sm focus:border-blue-500 focus:ring-blue-500 py-2.5">
                                <span class="ml-2 text-gray-500 text-sm font-mono whitespace-nowrap">
                                    / {{ editingAnime?.episodes ? editingAnime.episodes : '?' }}
                                </span>
                            </div>
                        </div>

                        <div class="flex-1">
                            <label class="block text-sm font-bold text-gray-700 mb-1">Note / 10</label>
                            <input type="number" v-model="form.score" min="0" max="10"
                                class="w-full rounded-lg border-gray-300 bg-white text-gray-900 shadow-sm focus:border-blue-500 focus:ring-blue-500 py-2.5">
                        </div>
                    </div>

                    <div class="pt-4 border-t border-gray-100">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Épingler au Panthéon</label>

                        <div class="flex gap-2 bg-gray-50 p-1.5 rounded-xl border border-gray-200">
                            <button type="button" @click="form.pantheon_rank = 1"
                                :class="form.pantheon_rank === 1 ? 'bg-yellow-500 text-white shadow-md scale-105' : 'text-gray-600 hover:bg-gray-200 bg-white border border-gray-100'"
                                class="flex-1 py-2 rounded-lg text-xs font-extrabold transition-all duration-200">
                                🥇 #1
                            </button>

                            <button type="button" @click="form.pantheon_rank = 2"
                                :class="form.pantheon_rank === 2 ? 'bg-gray-400 text-white shadow-md scale-105' : 'text-gray-600 hover:bg-gray-200 bg-white border border-gray-100'"
                                class="flex-1 py-2 rounded-lg text-xs font-extrabold transition-all duration-200">
                                🥈 #2
                            </button>

                            <button type="button" @click="form.pantheon_rank = 3"
                                :class="form.pantheon_rank === 3 ? 'bg-amber-600 text-white shadow-md scale-105' : 'text-gray-600 hover:bg-gray-200 bg-white border border-gray-100'"
                                class="flex-1 py-2 rounded-lg text-xs font-extrabold transition-all duration-200">
                                🥉 #3
                            </button>

                            <button type="button" v-if="form.pantheon_rank !== null" @click="form.pantheon_rank = null"
                                class="px-3 py-2 rounded-lg text-xs font-bold text-red-500 hover:bg-red-50 hover:text-red-700 transition-all duration-200 border border-transparent hover:border-red-100">
                                Retirer
                            </button>
                        </div>
                        <p class="text-[10px] text-gray-400 mt-1.5 italic px-1 flex items-center gap-1">
                            <span class="text-blue-500">ℹ</span> Remplacera l'animé actuel si ce rang est pris.
                        </p>
                    </div>

                </div>

                <div class="bg-gray-50 px-6 py-4 flex justify-between items-center border-t border-gray-100 shrink-0">
                    <button @click="deleteAnime"
                        class="text-red-600 hover:text-red-800 text-sm font-bold hover:underline transition flex items-center gap-1">
                        <Trash2 class="w-4 h-4" /> Supprimer
                    </button>

                    <div class="flex gap-3">
                        <button @click="closeModal"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                            Annuler
                        </button>
                        <button @click="saveChanges"
                            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 shadow-md transition hover:shadow-lg">
                            Sauvegarder
                        </button>
                    </div>
                </div>

            </div>
        </div>

    </AppLayout>
</template>