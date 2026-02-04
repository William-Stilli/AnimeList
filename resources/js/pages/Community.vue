<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    users: Array
});
</script>

<template>

    <Head title="Communauté" />

    <AppLayout>
        <div class="min-h-screen bg-gray-50/50 pb-20 pt-10">

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="mb-10 text-center sm:text-left">
                    <h1 class="text-3xl font-black text-gray-900 tracking-tight mb-2">
                        Explorateurs
                    </h1>
                    <p class="text-gray-500">Découvre les collections des autres membres.</p>
                </div>

                <div v-if="users.length > 0"
                    class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">

                    <Link v-for="user in users" :key="user.id" :href="route('user.list', user)"
                        class="group relative bg-white rounded-2xl p-6 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 border border-gray-100 flex flex-col items-center text-center overflow-hidden">
                        <div
                            class="absolute inset-0 bg-gradient-to-br from-blue-50 to-violet-50 opacity-0 group-hover:opacity-100 transition-opacity duration-500">
                        </div>

                        <div
                            class="relative z-10 h-20 w-20 rounded-full bg-gradient-to-tr from-blue-500 to-violet-600 shadow-lg flex items-center justify-center text-3xl font-black text-white mb-4 group-hover:scale-110 transition-transform duration-300">
                            {{ user.name.charAt(0).toUpperCase() }}
                        </div>

                        <div class="relative z-10">
                            <h3 class="text-lg font-bold text-gray-900 group-hover:text-blue-600 transition-colors">
                                {{ user.name }}
                            </h3>
                            <div
                                class="flex items-center justify-center gap-2 mt-2 text-sm text-gray-500 font-medium bg-gray-50 px-3 py-1 rounded-full group-hover:bg-white/80 transition-colors">
                                <span>{{ user.animes_count }} Animés</span>
                            </div>
                        </div>

                        <div
                            class="absolute bottom-0 inset-x-0 h-1 bg-blue-600 scale-x-0 group-hover:scale-x-100 transition-transform duration-300 origin-left">
                        </div>
                    </Link>

                </div>

                <div v-else class="text-center py-20 bg-white rounded-3xl border border-dashed border-gray-200">
                    <h3 class="text-lg font-bold text-gray-900">Il n'y a que toi ici...</h3>
                    <p class="text-gray-500 mt-1">Invite des amis à rejoindre le serveur !</p>
                </div>

            </div>
        </div>
    </AppLayout>
</template>