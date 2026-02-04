<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    users: Array
});

const getBannerGradient = (id) => {
    const gradients = [
        'from-blue-400 to-indigo-500',
        'from-purple-400 to-pink-500',
        'from-emerald-400 to-teal-500',
        'from-orange-400 to-red-500',
        'from-cyan-400 to-blue-500'
    ];
    return gradients[id % gradients.length];
};
</script>

<template>

    <Head title="Communauté" />

    <AppLayout>
        <div class="min-h-screen bg-gray-50 pb-20 pt-12">

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

                <div class="text-center mb-16 space-y-4">
                    <h1 class="text-4xl md:text-5xl font-black text-gray-900 tracking-tight">
                        Explorer les listes d'autres utilisateurs
                    </h1>
                    <p class="text-lg text-gray-500 max-w-2xl mx-auto">
                        Découvrez les collections des autres utilisateurs et comparez vos goûts.
                        <span class="font-bold text-blue-600">{{ users.length }} membres</span> inscrits.
                    </p>
                </div>

                <div v-if="users.length > 0"
                    class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-8">

                    <Link v-for="user in users" :key="user.id" :href="route('user.list', user)"
                        class="group relative bg-white rounded-2xl shadow-sm hover:shadow-2xl hover:-translate-y-2 transition-all duration-300 overflow-hidden flex flex-col">
                        <div :class="['h-24 bg-gradient-to-r', getBannerGradient(user.id)]"></div>

                        <div class="px-6 relative flex justify-center">
                            <div
                                class="-mt-12 h-24 w-24 rounded-full border-4 border-white bg-white shadow-md flex items-center justify-center overflow-hidden">
                                <div
                                    class="h-full w-full bg-gray-100 flex items-center justify-center text-3xl font-black text-gray-700 group-hover:bg-gray-800 group-hover:text-white transition-colors duration-300">
                                    {{ user.name.charAt(0).toUpperCase() }}
                                </div>
                            </div>
                        </div>

                        <div class="p-6 text-center flex-grow flex flex-col justify-between">
                            <div>
                                <h3
                                    class="text-xl font-bold text-gray-900 mb-1 group-hover:text-blue-600 transition-colors">
                                    {{ user.name }}
                                </h3>
                            </div>

                            <div class="mt-6">
                                <span
                                    class="inline-flex items-center px-4 py-2 rounded-full text-sm font-bold bg-blue-50 text-blue-700 border border-blue-100 group-hover:bg-blue-600 group-hover:text-white transition-all duration-300">
                                    {{ user.animes_count }} Animés
                                </span>
                            </div>
                        </div>

                        <div
                            class="bg-gray-50 px-6 py-3 border-t border-gray-100 flex justify-between items-center group-hover:bg-blue-50 transition-colors">
                            <span
                                class="text-xs font-semibold text-gray-400 uppercase tracking-wider group-hover:text-blue-600">Voir
                                le profil</span>
                        </div>
                    </Link>

                </div>

                <div v-else
                    class="flex flex-col items-center justify-center py-20 text-center bg-white rounded-3xl shadow-sm border border-gray-100">
                    <h3 class="text-xl font-bold text-gray-900">C'est bien vide ici...</h3>
                    <p class="text-gray-500 mt-2">Invitez des amis pour peupler ce monde !</p>
                </div>

            </div>
        </div>
    </AppLayout>
</template>