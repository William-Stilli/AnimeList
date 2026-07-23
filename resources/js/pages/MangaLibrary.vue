<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, Link } from '@inertiajs/vue3';
import { ref, computed, onMounted, watch } from 'vue';
import axios from 'axios';
import { useToast } from 'vue-toastification';
import { useAutoAnimate } from '@formkit/auto-animate/vue'
import AnimeToast from '@/components/AnimeToast.vue';
import confetti from 'canvas-confetti';
import { Crown, Trash2 } from 'lucide-vue-next';

const [parent] = useAutoAnimate()
const toast = useToast();

const minScore = ref(0);
const maxScore = ref(10);
const searchQuery = ref('');
const currentTab = ref('all');

const mangas = ref([]);
const tabs = [
    { key: 'all', label: 'Tout' },
    { key: 'reading', label: 'En cours' },
    { key: 'completed', label: 'Terminé' },
    { key: 'plan_to_read', label: 'À lire' },
    { key: 'dropped', label: 'Abandonné' }
];

const isModalOpen = ref(false);
const editingManga = ref(null);

const form = ref({
    status: '',
    chapters_read: 0,
    volumes_owned: 0,
    score: 0,
    pantheon_rank: null,
});

watch(() => form.value.status, (newStatus) => {
    if (newStatus === 'completed' && editingManga.value?.chapters) {
        form.value.chapters_read = editingManga.value.chapters;
    }
});

watch(maxScore, (newValue) => {
    if (newValue < minScore.value) maxScore.value = minScore.value;
});
watch(minScore, (newValue) => {
    if (newValue > maxScore.value) minScore.value = maxScore.value;
});

onMounted(async () => {
    refreshLibrary();
});

const refreshLibrary = async () => {
    try {
        const response = await axios.get('/my-mangas'); 
        mangas.value = response.data;
    } catch (error) {
        console.error("Erreur chargement bibliothèque manga:", error);
    }
};

const filteredMangas = computed(() => {
    let result = currentTab.value === 'all' 
        ? mangas.value 
        : mangas.value.filter(manga => manga.pivot.status === currentTab.value);

    if (searchQuery.value) {
        const lowerQuery = searchQuery.value.toLowerCase();
        result = result.filter(manga => { 
            return manga.genres && manga.genres.some(genre => genre.name.toLowerCase().includes(lowerQuery)) 
        });
    }

    result = result.filter(manga => {
        const score = manga.pivot.score;
        return score >= minScore.value && score <= maxScore.value;
    });

    return result;
});

const openEditModal = (manga) => {
    editingManga.value = manga;

    form.value = {
        status: manga.pivot.status || 'reading',
        chapters_read: manga.pivot.chapters_read || 0,
        volumes_owned: manga.pivot.volumes_owned || 0,
        score: manga.pivot.score || 0,
        pantheon_rank: manga.pivot.pantheon_rank || null,
    };

    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    setTimeout(() => { editingManga.value = null; }, 300);
};

const toggleStu = () => {
    if (!editingManga.value) return;

    const currentState = editingManga.value.pivot.is_stu;
    editingManga.value.pivot.is_stu = !currentState;

    router.post(`/mangas/${editingManga.value.mal_id}/stu`, {}, {
        preserveScroll: true,
        onSuccess: () => {
            const mangaTitle = editingManga.value.title_english || editingManga.value.title;
            if (editingManga.value.pivot.is_stu) {
                toast.success(mangaTitle + " a été élu S.T.U. !");
            } else {
                toast.info(mangaTitle + " a abdiqué de son trône.");
            }
            refreshLibrary();
        },
        onError: () => {
            editingManga.value.pivot.is_stu = currentState;
        }
    });
};

const saveChanges = async () => {
    if (!editingManga.value) return;

    if (editingManga.value.chapters && form.value.chapters_read > editingManga.value.chapters) {
        toast.warning(`${editingManga.value.title} n'a que ${editingManga.value.chapters} chapitres.`);
        form.value.chapters_read = editingManga.value.chapters;
        return;
    }

    try {
        await axios.post(`/mangas/${editingManga.value.id}`, {
            ...form.value,
            _method: 'PUT'
        });

        const index = mangas.value.findIndex(m => m.id === editingManga.value.id);
        if (index !== -1) {
            mangas.value[index].pivot.status = form.value.status;
            mangas.value[index].pivot.chapters_read = form.value.chapters_read;
            mangas.value[index].pivot.volumes_owned = form.value.volumes_owned;
            mangas.value[index].pivot.score = form.value.score;

            if (form.value.pantheon_rank !== null) {
                mangas.value.forEach(m => {
                    if (m.id !== editingManga.value.id && m.pivot.pantheon_rank === form.value.pantheon_rank) {
                        m.pivot.pantheon_rank = null;
                    }
                });
            }
            mangas.value[index].pivot.pantheon_rank = form.value.pantheon_rank;
        }

        if (form.value.status === 'completed') {
            confetti({ particleCount: 150, spread: 70, origin: { y: 0.6 } })
        }

        toast.success({
            component: AnimeToast,
            props: {
                title: editingManga.value.title,
                image: editingManga.value.image_url,
                message: "Mise à jour réussie"
            }
        }, { timeout: 3000, icon: false });

        closeModal();
    } catch (error) {
        toast.error("Erreur lors de la sauvegarde !");
        console.error(error);
    }
};

const deleteManga = async () => {
    if (!editingManga.value) return;
    if (!confirm(`Confirmer la suppression de "${editingManga.value.title}" de la liste ?`)) return;

    try {
        await axios.delete(`/mangas/${editingManga.value.id}`);
        mangas.value = mangas.value.filter(m => m.id !== editingManga.value.id);
        toast.warning("Manga supprimé !");
        closeModal();
    } catch (error) {
        console.error("Impossible de supprimer :", error);
        toast.error("Erreur lors de la suppression.");
    }
};
</script>

<template>
    <Head title="Bibliothèque Manga" />

    <AppLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Bibliothèque Manga</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        
                        <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-6">
                            <div class="flex space-x-2 overflow-x-auto pb-2 w-full md:w-auto shrink-0">
                                <button v-for="tab in tabs" :key="tab.key" @click="currentTab = tab.key"
                                    :class="['px-4 py-2 rounded-full font-bold text-sm transition', currentTab === tab.key ? 'bg-red-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200']">
                                    {{ tab.label }}
                                </button>
                            </div>

                            <div class="flex flex-col md:flex-row gap-6 p-4 bg-gray-50 rounded-xl border border-gray-100 w-full md:w-auto flex-1">
                                <div class="flex-1">
                                    <label class="block text-sm font-medium text-gray-600 mb-2">
                                        Score Min : <span class="text-blue-500 font-bold">{{ minScore }}</span>
                                    </label>
                                    <input type="range" v-model.number="minScore" min="0" max="10" step="1"
                                        class="w-full h-2 bg-gray-300 rounded-lg appearance-none cursor-pointer accent-blue-500">
                                </div>

                                <div class="flex-1">
                                    <label class="block text-sm font-medium text-gray-600 mb-2">
                                        Score Max : <span class="text-purple-500 font-bold">{{ maxScore }}</span>
                                    </label>
                                    <input type="range" v-model.number="maxScore" min="0" max="10" step="1"
                                        class="w-full h-2 bg-gray-300 rounded-lg appearance-none cursor-pointer accent-purple-500">
                                </div>
                                
                                <div class="flex-1 flex items-end">
                                    <input v-model="searchQuery" type="text" placeholder="Filtrer par genre..."
                                        class="w-full bg-white px-3 py-2 rounded-lg border border-gray-300 focus:border-red-500 focus:ring-red-500 text-sm">
                                </div>
                            </div>
                        </div>

                        <div v-if="filteredMangas?.length > 0" ref="parent"
                            class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-6">

                            <Link v-for="manga in filteredMangas" :key="manga.id"
                                :href="route('mangas.show', manga.mal_id)" 
                                @contextmenu.prevent="openEditModal(manga)"
                                class="relative group rounded-xl overflow-hidden transition-all duration-300 hover:-translate-y-1 hover:shadow-xl flex flex-col cursor-pointer"
                                :class="manga.pivot.is_stu
                                    ? 'bg-gray-50 ring-4 ring-yellow-500 shadow-2xl shadow-yellow-500/20 scale-[1.02] z-10'
                                    : 'bg-white shadow-sm border border-gray-100'"
                                title="Clic gauche: Détails | Clic droit: Modifier">

                                <div class="block relative aspect-[2/3] overflow-hidden bg-gray-200">
                                    <img :src="manga.image_url" :alt="manga.title"
                                        class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110">

                                    <div v-if="manga.pivot.is_stu"
                                        class="absolute top-0 left-0 w-full z-20 bg-gradient-to-b from-black/90 via-black/60 to-transparent pt-3 pb-6">
                                        <div class="flex items-center gap-2 justify-center text-yellow-400 font-black tracking-widest text-xs uppercase drop-shadow-[0_2px_2px_rgba(0,0,0,0.8)]">
                                            <Crown class="w-4 h-4 fill-current animate-pulse" /> S.T.U.
                                        </div>
                                    </div>

                                    <div v-if="manga.pivot.pantheon_rank"
                                        class="absolute top-2 left-2 w-8 h-8 rounded-full flex items-center justify-center font-black text-white shadow-lg border-2 z-20"
                                        :class="{
                                            'bg-yellow-500 border-yellow-300 shadow-yellow-500/50': manga.pivot.pantheon_rank === 1,
                                            'bg-gray-400 border-gray-300 shadow-gray-400/50': manga.pivot.pantheon_rank === 2,
                                            'bg-amber-600 border-amber-300 shadow-amber-600/50': manga.pivot.pantheon_rank === 3
                                        }">
                                        #{{ manga.pivot.pantheon_rank }}
                                    </div>

                                    <div v-if="manga.pivot.score > 0"
                                        class="absolute top-2 right-2 bg-black/70 backdrop-blur-md text-white font-bold px-2 py-1 rounded-lg text-xs border border-white/10 z-10">
                                        <span class="text-yellow-400">★</span> {{ manga.pivot.score }}
                                    </div>

                                    <div class="absolute inset-0 bg-black/10 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                                </div>

                                <div class="p-4 flex flex-col flex-1 gap-2">
                                    <h3 class="font-bold truncate text-sm"
                                        :class="manga.pivot.is_stu ? 'text-yellow-600' : 'text-gray-900'">
                                        {{ manga.title }}
                                    </h3>

                                    <div class="flex flex-wrap gap-1 opacity-70">
                                        <span v-for="g in manga.genres?.slice(0, 2)" :key="g.id"
                                            class="text-[10px] bg-gray-100 px-1.5 py-0.5 rounded text-gray-600 font-medium">
                                            {{ g.name }}
                                        </span>
                                    </div>

                                    <div class="mt-auto pt-2">
                                        <div class="flex justify-between text-xs text-gray-500 font-bold mb-1">
                                            <span>Ch: {{ manga.pivot.chapters_read || 0 }}</span>
                                            <span v-if="manga.chapters">/ {{ manga.chapters }}</span>
                                        </div>
                                        <div class="w-full bg-gray-200 rounded-full h-1.5 overflow-hidden">
                                            <div class="h-full rounded-full transition-all duration-500"
                                                :class="manga.pivot.status === 'completed' ? 'bg-green-500' : (manga.pivot.is_stu ? 'bg-yellow-500' : 'bg-red-600')"
                                                :style="{ width: manga.chapters ? (manga.pivot.chapters_read / manga.chapters * 100) + '%' : (manga.pivot.status === 'completed' ? '100%' : '0%') }">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </Link>
                        </div>

                        <div v-else class="text-center py-16 bg-gray-50 rounded-2xl border-2 border-dashed border-gray-200">
                            <p class="text-gray-500 text-lg font-medium mb-4">Ta bibliothèque est vide pour ces filtres !</p>
                            <button @click="router.get(route('mangas.search'))" class="bg-red-600 hover:bg-red-500 text-white font-bold py-2 px-6 rounded-lg shadow transition">
                                Chercher une romance tranquille
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="isModalOpen"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/80 backdrop-blur-sm">
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-md overflow-hidden transform transition-all border border-gray-100 flex flex-col max-h-[90vh]">

                <div class="bg-gray-50 px-6 py-4 border-b border-gray-100 flex justify-between items-center shrink-0">
                    <h3 class="font-bold text-lg text-gray-800 truncate pr-4">{{ editingManga?.title }}</h3>
                    <button @click="closeModal" class="text-gray-400 hover:text-red-500 text-2xl font-bold transition">&times;</button>
                </div>

                <div class="p-6 space-y-5 overflow-y-auto custom-scrollbar">
                    
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Statut</label>
                        <select v-model="form.status"
                            class="w-full rounded-lg border-gray-300 bg-white text-gray-900 shadow-sm focus:border-red-500 focus:ring-red-500 py-2.5">
                            <option value="reading" class="text-gray-900">En cours de lecture</option>
                            <option value="completed" class="text-gray-900">Terminé</option>
                            <option value="plan_to_read" class="text-gray-900">À lire plus tard</option>
                            <option value="dropped" class="text-gray-900">Abandonné</option>
                        </select>
                    </div>

                    <div class="flex gap-4">
                        <div class="flex-1">
                            <label class="block text-sm font-bold text-gray-700 mb-1">Chapitres lus</label>
                            <div class="flex items-center">
                                <input type="number" v-model="form.chapters_read" min="0" :max="editingManga?.chapters || null"
                                    class="w-full rounded-lg border-gray-300 bg-white text-gray-900 shadow-sm focus:border-red-500 focus:ring-red-500 py-2.5">
                                <span v-if="editingManga?.chapters" class="ml-2 text-gray-500 text-sm font-mono whitespace-nowrap">
                                    / {{ editingManga.chapters }}
                                </span>
                            </div>
                        </div>

                        <div class="flex-1">
                            <label class="block text-sm font-bold text-gray-700 mb-1">Volumes (Physique)</label>
                            <input type="number" v-model="form.volumes_owned" min="0"
                                class="w-full rounded-lg border-gray-300 bg-white text-gray-900 shadow-sm focus:border-orange-500 focus:ring-orange-500 py-2.5">
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Note / 10</label>
                        <input type="number" v-model="form.score" min="0" max="10"
                            class="w-full rounded-lg border-gray-300 bg-white text-gray-900 shadow-sm focus:border-purple-500 focus:ring-purple-500 py-2.5">
                    </div>

                    <div class="pt-5 mt-4 border-t border-gray-100 flex justify-between items-center">
                        <div>
                            <label class="block text-sm font-bold text-gray-700">Élire comme meilleur manga</label>
                        </div>
                        <button type="button" @click="toggleStu"
                            :class="editingManga?.pivot?.is_stu
                                ? 'bg-yellow-500 text-white shadow-lg shadow-yellow-500/40 ring-2 ring-yellow-400 ring-offset-2 scale-105'
                                : 'bg-white text-gray-500 border border-gray-200 hover:border-yellow-400 hover:text-yellow-600 hover:bg-yellow-50'"
                            class="px-4 py-2 rounded-xl text-xs font-black transition-all duration-300 flex items-center gap-2">
                            <Crown class="w-4 h-4" :class="editingManga?.pivot?.is_stu ? 'animate-bounce' : ''" />
                            {{ editingManga?.pivot?.is_stu ? 'Abdiquer' : 'Élire S.T.U.' }}
                        </button>
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
                            <span class="text-blue-500">ℹ</span> Remplacera le manga actuel si ce rang est pris.
                        </p>
                    </div>

                </div>

                <div class="bg-gray-50 px-6 py-4 flex justify-between items-center border-t border-gray-100 shrink-0">
                    <button @click="deleteManga"
                        class="text-red-600 hover:text-red-800 text-sm font-bold hover:underline transition flex items-center gap-1">
                        <Trash2 class="w-4 h-4" /> Supprimer
                    </button>

                    <div class="flex gap-3">
                        <button @click="closeModal"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                            Annuler
                        </button>
                        <button @click="saveChanges"
                            class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 shadow-md transition hover:shadow-lg">
                            Sauvegarder
                        </button>
                    </div>
                </div>

            </div>
        </div>

    </AppLayout>
</template>