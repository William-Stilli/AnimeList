<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
});

const backgrounds = [
    '/images/saber.jfif', 
    '/images/violet.jfif',
    '/images/klein.jfif',
];

const currentIndex = ref(0);
let interval;

onMounted(() => {
    interval = setInterval(() => {
        currentIndex.value = (currentIndex.value + 1) % backgrounds.length;
    }, 5000);
});

onUnmounted(() => {
    clearInterval(interval);
});
</script>

<template>
    <Head title="Welcome" />

    <div class="relative min-h-screen flex flex-col items-center justify-center overflow-hidden bg-gray-900">
        
        <transition-group name="fade" tag="div">
            <div v-for="(bg, index) in backgrounds" :key="bg"
                 v-show="currentIndex === index"
                 class="absolute inset-0 z-0 bg-cover bg-center bg-no-repeat transition-opacity duration-1000"
                 :style="{ backgroundImage: `url(${bg})` }">
            </div>
        </transition-group>

        <div class="absolute inset-0 bg-black/60 z-10"></div>

        <div class="relative z-20 text-center px-6 flex-grow flex flex-col justify-center">
            <h1 class="text-5xl md:text-7xl font-bold text-white mb-6 tracking-tight drop-shadow-lg">
                Ultimate Anime Tracker
            </h1>
            <p class="text-xl text-gray-300 mb-10 max-w-2xl mx-auto drop-shadow">
                Gère ta bibliothèque, gagne de l'XP et débloque des badges.
            </p>

            <div v-if="canLogin" class="flex flex-col sm:flex-row justify-center gap-4">
                
                <Link v-if="$page.props.auth.user" :href="route('dashboard')"
                      class="px-8 py-3 bg-red-600 hover:bg-red-700 text-white font-bold rounded-lg transition-colors shadow-lg">
                    Retour au Dashboard
                </Link>

                <template v-else>
                    <Link :href="route('login')"
                          class="px-8 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-lg transition-all shadow-[0_0_15px_rgba(79,70,229,0.5)]">
                        Connexion
                    </Link>

                    <Link v-if="canRegister" :href="route('register')"
                          class="px-8 py-3 bg-transparent border-2 border-white/70 hover:border-white hover:bg-white hover:text-black text-white font-bold rounded-lg transition-all">
                        Inscription
                    </Link>
                </template>
                
            </div>
        </div>

        <div class="relative z-20 w-full text-center pb-4 px-4">
            <p class="text-xs text-gray-400/80 drop-shadow-md">
                Les images d'illustration appartiennent à leurs créateurs et studios respectifs.<br class="hidden md:inline">
                Aucun usage commercial n'en est fait. Projet développé à des fins strictement personnelles.
            </p>
        </div>
        
    </div>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active {
    transition: opacity 1.5s ease-in-out;
}
.fade-enter-from, .fade-leave-to {
    opacity: 0;
}
</style>