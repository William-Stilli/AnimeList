<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import axios from 'axios';
import draggable from 'vuedraggable';
import { useToast } from "vue-toastification";

const toast = useToast();
const myRanking = ref([]);
const loading = ref(false);

onMounted(async () => {
    loadRanking();
});

const loadRanking = async () => {
    const response = await axios.get('/api/manual-ranking');
    myRanking.value = response.data;
}

const onDragEnd = async () => {
    loading.value = true;
    const orderedIds = myRanking.value.map(anime => anime.id);

    try {
        await axios.post('/api/reorder', { animes: orderedIds });
        toast.success("Classement MàJ");
    } catch (error) {
        console.error(error);
        toast.error("Erreur de sauvegarde");
    } finally {
        loading.value = false;
    }
}
</script>

<template>

    <Head title="Ma Tier List" />

    <AppLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Ma Tier List (Drag & Drop)
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

                <div class="bg-gray-50 p-4 rounded-lg border border-gray-200 mb-4 text-sm text-gray-600">
                    Attrape un animé et glisse-le pour changer son rang
                </div>

                <draggable v-model="myRanking" item-key="id" @end="onDragEnd" class="space-y-3" ghost-class="opacity-50"
                    drag-class="scale-105">
                    <template #item="{ element, index }">
                        <div
                            class="bg-white p-4 rounded-xl shadow-sm border border-gray-200 flex items-center cursor-move hover:shadow-md transition active:cursor-grabbing select-none">

                            <div class="font-black text-3xl text-gray-200 w-16 text-center italic">
                                #{{ index + 1 }}
                            </div>

                            <img :src="element.image_url"
                                class="w-16 h-16 object-cover rounded-lg shadow-sm mx-4 pointer-events-none">

                            <div class="flex-1">
                                <h3 class="font-bold text-gray-800 text-lg">
                                    {{ element.title_english || element.title }}
                                </h3>
                                <div class="flex items-center gap-2 text-sm text-gray-500">
                                    <span v-if="element.pivot.score" class="text-yellow-600 font-bold">★ {{
                                        element.pivot.score }}</span>
                                    <span>• {{ element.episodes }} éps</span>
                                </div>
                            </div>

                            <div class="text-gray-300 pr-4">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 8h16M4 16h16"></path>
                                </svg>
                            </div>

                        </div>
                    </template>
                </draggable>

                <div v-if="myRanking.length === 0" class="text-center py-12 text-gray-400">
                    Aucun animé terminé à classer pour l'instant !
                </div>

            </div>
        </div>
    </AppLayout>
</template>