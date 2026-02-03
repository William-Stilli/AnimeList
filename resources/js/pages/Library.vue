<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed, onMounted, watch } from 'vue';
import axios, { spread } from 'axios';
import { useToast } from 'vue-toastification';
import { useAutoAnimate } from '@formkit/auto-animate/vue'
import AnimeToast from '@/components/AnimeToast.vue';
import confetti from 'canvas-confetti';

const [parent] = useAutoAnimate()
const toast = useToast();

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
    score: 0
});

watch(() => form.value.status, (newStatus) => {
    if (newStatus === 'completed' && editingAnime.value?.episodes) {
        form.value.progress = editingAnime.value.episodes;
    }
});

const galleryImages = ref([]);
const isLoadingGallery = ref(false);

onMounted(async () => {
    refreshLibrary();
});

const refreshLibrary = async () => {
    try {
        const response = await axios.get('/my-animes');
        animes.value = response.data;
    } catch (error) {
        console.error("Erreur:", error);
    }
};

const filteredAnimes = computed(() => {
    let result = currentTab.value === 'all' ? animes.value : animes.value.filter(anime => anime.pivot.status === currentTab.value);

    if (searchQuery.value) {
        const lowerQuery = searchQuery.value.toLowerCase();
        result = result.filter(anime => { return anime.genres && anime.genres.some(genre => genre.name.toLowerCase().includes(lowerQuery)) });
    }
    return result
});

const statusLabel = (status) => {
    const map = { watching: 'En cours', completed: 'Fini', plan_to_watch: 'À voir', dropped: 'Stop' };
    return map[status] || status;
};

const openEditModal = async (anime) => {
    editingAnime.value = anime;
    form.value = {
        status: anime.pivot.status,
        progress: anime.pivot.progress,
        score: anime.pivot.score
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
        console.error("ERREUR APPEL API :", error);
    } finally {
        isLoadingGallery.value = false;
    }
};

const closeModal = () => {
    isModalOpen.value = false;
    editingAnime.value = null;
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
        }

        if (form.value.status === 'completed') {
            confetti({
                particleCount: 150,
                spread: 70,
                origin: { y: 0.6 }
            })
        }

        toast.success(
            {
                component: AnimeToast,
                props: {
                    title: editingAnime.value.title,
                    image: editingAnime.value.image_url,
                    message: "Mise à jour réussie"
                }
            },
            {
                timeout: 3000,
                icon: false,
            }
        )

        closeModal();
    } catch (error) {
        toast.error("Erreur lors de la sauvegarde !");
        console.error(error);
    }
};

const deleteAnime = async () => {
    if (!editingAnime.value) return;

    if (!confirm(`Confirmé la suppression de "${editingAnime.value.title}" de la liste`)) {
        return;
    }

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

const changeCover = async (newUrl) => {
    editingAnime.value.image_url = newUrl;
    const index = animes.value.findIndex(a => a.id === editingAnime.value.id);
    if (index !== -1) {
        animes.value[index].image_url = newUrl;
    }

    try {
        await axios.post(`/animes/${editingAnime.value.id}`, {
            ...form.value,
            image_url: newUrl,
            _method: 'PUT'
        });
        toast.success("Nouvelle couverture appliquée !");
    } catch (error) {
        console.error("Erreur lors du changement d'image", error);
        toast.error("Erreur lors du changement d'image");
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

                            <div class="relative w-full md:w-64">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </div>

                                <input v-model="searchQuery" type="text"
                                    placeholder="Filtrer par genre (ex: Isekai, Horror)..."
                                    class="pl-10 block w-full rounded-full border-gray-300 bg-gray-50 focus:border-blue-500 focus:ring-blue-500 sm:text-sm transition">
                            </div>
                        </div>

                        <div v-if="filteredAnimes.length > 0" ref="parent"
                            class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-6">
                            <div v-for="anime in filteredAnimes" :key="anime.id"
                                @click="router.visit(route('animes.show', anime.id))"
                                @contextmenu.prevent="openEditModal(anime)"
                                class="cursor-pointer group border rounded-lg overflow-hidden shadow hover:shadow-lg transition flex flex-col h-full bg-white relative">
                                <div class="h-48 overflow-hidden bg-gray-200 relative">
                                    <img :src="anime.image_url" :alt="anime.title"
                                        class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                                    <div
                                        class="absolute inset-0 bg-black/0 group-hover:bg-black/20 transition flex items-center justify-center">
                                        <span
                                            class="text-white opacity-0 group-hover:opacity-100 font-bold bg-black/50 px-3 py-1 rounded-full text-sm">Click
                                            droit pour modifier</span>
                                    </div>
                                </div>

                                <div class="p-3 flex flex-col flex-grow justify-between">
                                    <div class="flex flex-wrap gap-1 mb-1 h-5 overflow-hidden">
                                        <span v-for="g in anime.genres?.slice(0, 3)" :key="g.id"
                                            class="text-[10px] uppercase font-bold text-gray-400 bg-gray-50 px-1 rounded">
                                            {{ g.name }}
                                        </span>
                                    </div>

                                    <h3 class="font-bold truncate text-sm mb-2">{{ anime.title }}</h3>
                                    <div class="flex justify-between items-center mt-auto">
                                        <span
                                            class="text-xs px-2 py-1 rounded bg-gray-100 text-gray-600 font-medium border">{{
                                                statusLabel(anime.pivot.status) }}</span>
                                        <span class="text-xs text-gray-500 font-mono">Ep. {{ anime.pivot.progress
                                            }}</span>
                                    </div>
                                    <div v-if="anime.pivot.score" class="text-xs text-yellow-600 font-bold mt-1">★
                                        {{
                                            anime.pivot.score }}/10</div>
                                </div>
                            </div>
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
                class="bg-white rounded-xl shadow-2xl w-full max-w-md overflow-hidden transform transition-all border border-gray-100">

                <div class="bg-gray-50 px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                    <h3 class="font-bold text-lg text-gray-800 truncate pr-4">{{ editingAnime?.title }}</h3>
                    <button @click="closeModal"
                        class="text-gray-400 hover:text-red-500 text-2xl font-bold transition">&times;</button>
                </div>

                <div class="p-6 space-y-5">
                    <div>
                        <h4 class="font-bold text-gray-700 mb-3 flex items-center gap-2">
                            Galerie Officielle
                            <span v-if="isLoadingGallery"
                                class="text-xs font-normal text-gray-400 animate-pulse">(Chargement...)</span>
                        </h4>

                        <div v-if="galleryImages.length > 0"
                            class="grid grid-cols-4 gap-2 max-h-40 overflow-y-auto pr-2 custom-scrollbar">
                            <div v-for="(img, index) in galleryImages" :key="index"
                                class="relative group cursor-pointer" @click="changeCover(img.jpg.image_url)"> <img
                                    :src="img.jpg.image_url"
                                    class="w-full h-24 object-cover rounded-md border border-gray-200 hover:scale-105 transition duration-300">
                                <div class="absolute inset-0 bg-black/0 group-hover:bg-black/10 transition rounded-md">
                                </div>
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
                                    class="w-full rounded-lg border-gray-300 bg-white text-gray-900 shadow-sm focus:border-blue-500 focus:ring-blue-500 py-2.5">
                                <span class="ml-2 text-gray-500 text-sm font-mono whitespace-nowrap"
                                    v-if="editingAnime?.episodes">/
                                    {{ editingAnime.episodes }}</span>
                            </div>
                        </div>

                        <div class="flex-1">
                            <label class="block text-sm font-bold text-gray-700 mb-1">Note / 10</label>
                            <input type="number" v-model="form.score" min="0" max="10"
                                class="w-full rounded-lg border-gray-300 bg-white text-gray-900 shadow-sm focus:border-blue-500 focus:ring-blue-500 py-2.5">
                        </div>
                    </div>

                </div>

                <div class="bg-gray-50 px-6 py-4 flex justify-between items-center border-t border-gray-100">

                    <button @click="deleteAnime"
                        class="text-red-600 hover:text-red-800 text-sm font-bold hover:underline transition">
                        Supprimer
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