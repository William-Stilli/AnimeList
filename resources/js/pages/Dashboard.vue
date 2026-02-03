<script setup lang="ts">
import { Head, usePage, Link, router } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Play, Trophy, Clock, Tv } from 'lucide-vue-next';
import { type BreadcrumbItem } from '@/types';
import PlaceholderPattern from '../components/PlaceholderPattern.vue';
import { useToast } from 'vue-toastification';

const toast = useToast();

defineProps({
    watching: Array,
    stats: Object,
})

const page = usePage();
const user = computed(() => page.props.auth.user)

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: `Welcome ${user.value.name}`,
        href: dashboard().url,
    },
];

const incrementProgress = (anime: any) => {
    const nextProgress = Number(anime.pivot.progress) + 1;

    router.post(route('animes.update', anime.id), {
        _method: 'PUT',
        progress: nextProgress
    }, {
        preserveScroll: true,
        onSuccess: () => console.log("Episode +1 validé !")
    });
};
</script>

<template>

    <Head title="Dashboard" />

    <AppLayout>
        <div class="p-6 space-y-8">

            <div class="flex items-center justify-between">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                    Bonjour, {{ user.name }}
                </h1>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <Card>
                    <CardContent class="flex items-center gap-4 p-6">
                        <div class="p-3 bg-blue-100 dark:bg-blue-900 rounded-full">
                            <Tv class="w-6 h-6 text-blue-600 dark:text-blue-300" />
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Épisodes Vus</p>
                            <h3 class="text-2xl font-bold">{{ stats.episodes }}</h3>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent class="flex items-center gap-4 p-6">
                        <div class="p-3 bg-purple-100 dark:bg-purple-900 rounded-full">
                            <Clock class="w-6 h-6 text-purple-600 dark:text-purple-300" />
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Temps de visionnage total</p>
                            <h3 class="text-2xl font-bold">{{ stats.time_spent }}</h3>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent class="flex items-center gap-4 p-6">
                        <div class="p-3 bg-yellow-100 dark:bg-yellow-900 rounded-full">
                            <Trophy class="w-6 h-6 text-yellow-600 dark:text-yellow-300" />
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Animés Terminés</p>
                            <h3 class="text-2xl font-bold">{{ stats.completed_count }}</h3>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <div v-if="watching.length > 0">
                <h2 class="text-xl font-semibold mb-4 flex items-center gap-2">
                    <Play class="w-5 h-5 text-red-500" /> En cours de visionnage
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div v-for="anime in watching" :key="anime.id" class="group relative">
                        <div
                            class="relative overflow-hidden rounded-xl shadow-lg transition-transform hover:scale-105 bg-white dark:bg-gray-800">
                            <div class="aspect-video relative">
                                <img :src="anime.image_url" class="w-full h-full object-cover" />
                                <div
                                    class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                    <Link :href="route('animes.show', anime.id)">
                                        <Button variant="secondary" size="sm">Voir Détails</Button>
                                    </Link>
                                </div>
                            </div>

                            <div class="p-4">
                                <h3 class="font-bold truncate" :title="anime.title">{{ anime.title }}</h3>

                                <div class="mt-3 flex items-center justify-between text-sm text-gray-500 mb-1">
                                    <span>Ep {{ anime.pivot.progress }}</span>
                                    <span>{{ anime.episodes ? 'sur ' + anime.episodes : '?' }}</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2 dark:bg-gray-700">
                                    <div class="bg-blue-600 h-2 rounded-full transition-all duration-500"
                                        :style="{ width: anime.episodes ? (anime.pivot.progress / anime.episodes * 100) + '%' : '0%' }">
                                    </div>
                                </div>

                                <div class="mt-4 flex justify-end">


                                    <Button size="sm" variant="outline" class="w-full gap-2">
                                        +1 Épisode
                                    </Button>

                                    <Button size="sm" variant="outline"
                                        class="w-full gap-2 cursor-pointer active:scale-95 transition-transform"
                                        @click="incrementProgress(anime)"
                                        :disabled="anime.episodes && anime.pivot.progress >= anime.episodes">
                                        <Play class="w-4 h-4" /> +1 Épisode
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div v-else
                class="text-center py-12 bg-gray-50 dark:bg-gray-800/50 rounded-xl border border-dashed border-gray-300">
                <p class="text-gray-500 mb-4">Aucun animé en cours... C'est le calme plat.</p>
                <Link :href="route('library')">
                    <Button>Explorer ma bibliothèque</Button>
                </Link>
            </div>

        </div>
    </AppLayout>
</template>