<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { useDragAndDrop } from '@formkit/drag-and-drop/vue';
import { ref, onMounted } from 'vue';
import axios from 'axios';

const props = defineProps({
    animes: {
        type: Array,
        default: () => []
    }
});

const sList = ref([]);
const aList = ref([]);
const bList = ref([]);
const cList = ref([]);
const poolList = ref([]);

onMounted(() => {
    if (props.animes) {
        props.animes.forEach(anime => {
            const r = anime.pivot.rank;
            if (r === 1) sList.value.push(anime);
            else if (r === 2) aList.value.push(anime);
            else if (r === 3) bList.value.push(anime);
            else if (r === 4) cList.value.push(anime);
            else poolList.value.push(anime);
        });
    }
});

const updateRank = async (node, rank) => {
    const anime = node.data.value;
    try {
        await axios.put(`/animes/${anime.id}`, { rank: rank });
    } catch (e) {
        console.error("Échec sauvegarde", e);
    }
};

const config = (rank, getList) => ({
    group: 'tiers',
    onTransfer: async (data) => {
        const node = data.draggedNodes ? data.draggedNodes[0] : null;

        if (!node) {
            return;
        }

        const anime = node.data.value;

        await new Promise(resolve => setTimeout(resolve, 50));

        const currentList = getList();
        const isArriving = currentList.some(a => a.id === anime.id);

        if (isArriving) {
            updateRank(node, rank);
        }
    }
});

const [sRef, sAnimes] = useDragAndDrop(sList, config(1, () => sAnimes.value));
const [aRef, aAnimes] = useDragAndDrop(aList, config(2, () => aAnimes.value));
const [bRef, bAnimes] = useDragAndDrop(bList, config(3, () => bAnimes.value));
const [cRef, cAnimes] = useDragAndDrop(cList, config(4, () => cAnimes.value));
const [poolRef, poolAnimes] = useDragAndDrop(poolList, config(null, () => poolAnimes.value));
</script>

<template>

    <Head title="Mon Classement" />

    <AppLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Tier List 🏆
            </h2>
        </template>

        <div class="py-12 bg-gray-100 min-h-screen">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4 select-none">

                <div class="flex shadow-md rounded-lg overflow-hidden bg-gray-200">
                    <div
                        class="w-24 bg-red-600 text-white font-black text-4xl flex items-center justify-center shrink-0">
                        S
                    </div>
                    <div ref="sRef" class="flex-1 p-4 min-h-[120px] flex flex-wrap gap-3 bg-[#ff7f7f]/20">
                        <div v-for="anime in sAnimes" :key="anime.id"
                            class="w-24 cursor-grab active:cursor-grabbing hover:scale-105 transition">
                            <img :src="anime.image_url"
                                class="rounded-md shadow border border-red-200 w-full h-36 object-cover pointer-events-none">
                        </div>
                    </div>
                </div>

                <div class="flex shadow-md rounded-lg overflow-hidden bg-gray-200">
                    <div
                        class="w-24 bg-orange-500 text-white font-black text-4xl flex items-center justify-center shrink-0">
                        A
                    </div>
                    <div ref="aRef" class="flex-1 p-4 min-h-[120px] flex flex-wrap gap-3 bg-[#ffbf7f]/20">
                        <div v-for="anime in aAnimes" :key="anime.id"
                            class="w-24 cursor-grab hover:scale-105 transition">
                            <img :src="anime.image_url"
                                class="rounded-md shadow border border-orange-200 w-full h-36 object-cover pointer-events-none">
                        </div>
                    </div>
                </div>

                <div class="flex shadow-md rounded-lg overflow-hidden bg-gray-200">
                    <div
                        class="w-24 bg-yellow-500 text-white font-black text-4xl flex items-center justify-center shrink-0">
                        B
                    </div>
                    <div ref="bRef" class="flex-1 p-4 min-h-[120px] flex flex-wrap gap-3 bg-[#ffff7f]/20">
                        <div v-for="anime in bAnimes" :key="anime.id"
                            class="w-24 cursor-grab hover:scale-105 transition">
                            <img :src="anime.image_url"
                                class="rounded-md shadow border border-yellow-200 w-full h-36 object-cover pointer-events-none">
                        </div>
                    </div>
                </div>

                <div class="flex shadow-md rounded-lg overflow-hidden bg-gray-200">
                    <div
                        class="w-24 bg-green-500 text-white font-black text-4xl flex items-center justify-center shrink-0">
                        C
                    </div>
                    <div ref="cRef" class="flex-1 p-4 min-h-[120px] flex flex-wrap gap-3 bg-[#7fff7f]/20">
                        <div v-for="anime in cAnimes" :key="anime.id"
                            class="w-24 cursor-grab hover:scale-105 transition">
                            <img :src="anime.image_url"
                                class="rounded-md shadow border border-green-200 w-full h-36 object-cover pointer-events-none">
                        </div>
                    </div>
                </div>

                <div class="mt-12">
                    <h3 class="font-bold text-gray-500 mb-2 uppercase tracking-wide text-sm">Non classés ({{
                        poolAnimes.length
                        }})</h3>
                    <div ref="poolRef"
                        class="bg-white p-4 rounded-lg shadow-inner min-h-[150px] flex flex-wrap gap-3 border-2 border-dashed border-gray-300">
                        <div v-for="anime in poolAnimes" :key="anime.id"
                            class="w-24 cursor-grab hover:scale-105 transition">
                            <img :src="anime.image_url"
                                class="rounded-md shadow w-full h-36 object-cover pointer-events-none opacity-80 hover:opacity-100">
                        </div>
                        <div v-if="poolAnimes.length === 0" class="w-full text-center text-gray-400 italic py-10">
                            Tout est classé ! Beau boulot.
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AppLayout>
</template>