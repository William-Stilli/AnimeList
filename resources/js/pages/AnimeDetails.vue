<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, onMounted, computed, watch } from 'vue';
import axios from 'axios';
import { useToast } from 'vue-toastification';

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
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl overflow-hidden flex flex-col md:flex-row">

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

                        <div v-if="!isEditing && myData" class="space-y-3">
                            <div class="flex justify-between text-sm">
                                <span class="text-white">Statut</span>
                                <span
                                    class="font-medium capitalize text-white dark:text-white px-2 py-0.5 rounded-full bg-gray-100 dark:bg-gray-700 text-xs">
                                    {{ myData.status.replace(/_/g, ' ') }}
                                </span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-white">Score</span>
                                <span class="font-bold text-yellow-500">★ {{ myData.score }}/10</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-white">Avancement</span>
                                <span class="font-medium">{{ myData.progress }} / {{ anime.episodes || '?' }} eps</span>
                            </div>

                            <div class="w-full bg-gray-200 rounded-full h-1.5 dark:bg-gray-700">
                                <div class="bg-blue-500 h-1.5 rounded-full"
                                    :style="{ width: (anime.episodes && anime.episodes > 0) ? Math.min((myData.progress / anime.episodes * 100), 100) + '%' : '0%' }">
                                </div>
                            </div>

                            <div v-if="myData.review"
                                class="mt-4 pt-4 border-t border-dashed border-gray-200 dark:border-gray-700">
                                <span class="text-xs font-bold text-white uppercase tracking-wide">Ma Critique</span>
                                <p class="text-sm text-gray-600 dark:text-gray-300 italic mt-1 whitespace-pre-wrap">"{{
                                    myData.review }}"</p>
                            </div>
                        </div>

                        <div v-else-if="isEditing || !myData" class="space-y-4">
                            <div v-if="!isEditing && !myData" class="text-center py-4">
                                <form @submit.prevent="form.post(route('animes.store'), {
                                    onSuccess: () => toast.success('Ajouté à la liste !')
                                })">
                                    <input type="hidden" name="mal_id" :value="anime.mal_id">
                                        <input type="hidden" name="title" :value="anime.title">
                                            <input type="hidden" name="image_url" :value="anime.image_url">
                                                <input type="hidden" name="episodes" :value="anime.episodes || ''">

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
                                        <span class="text-xs text-gray-400">{{ form.progress }} / {{ anime.episodes ||
                                            '?'
                                        }}</span>
                                    </div>
                                    <Input type="number" v-model="form.progress" min="0" :max="anime.episodes || 999"
                                        class="h-9 bg-gray-50 dark:bg-gray-900" />
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

                        <h2 class="text-2xl font-bold text-white mb-4">Synopsis</h2>
                        <p class="text-white leading-relaxed mb-6 whitespace-pre-line text-justify">
                            {{ jikanData.synopsis }}
                        </p>

                        <div v-if="jikanData.trailer?.embed_url" class="mt-8">
                            <h3 class="font-bold text-gray-800 mb-3">Trailer</h3>
                            <iframe class="w-full aspect-video rounded-lg shadow-md" :src="jikanData.trailer.embed_url"
                                allowfullscreen></iframe>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AppLayout>
</template>