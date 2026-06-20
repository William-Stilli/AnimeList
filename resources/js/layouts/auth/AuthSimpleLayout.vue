<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import { Link } from '@inertiajs/vue3';

defineProps<{
    title?: string;
    description?: string;
}>();

const backgrounds = [
    '/images/saber.jfif', 
    '/images/violet.jfif',
    '/images/klein.jfif',
];

const currentIndex = ref(0);
let interval: ReturnType<typeof setInterval>;

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
    <div class="relative min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-900 overflow-hidden">
        
        <transition-group name="fade" tag="div">
            <div v-for="(bg, index) in backgrounds" :key="bg"
                 v-show="currentIndex === index"
                 class="absolute inset-0 z-0 bg-cover bg-center bg-no-repeat transition-opacity duration-1000"
                 :style="{ backgroundImage: `url(${bg})` }">
            </div>
        </transition-group>

        <div class="absolute inset-0 bg-black/60 z-10"></div>

        <div class="relative z-20 w-full sm:max-w-md mt-6 px-6 py-8 bg-gray-900/70 backdrop-blur-md shadow-2xl overflow-hidden sm:rounded-xl border border-gray-700/50">
            
            <div class="flex flex-col items-center mb-6">
                <Link href="/" class="mb-4">
                    <span class="text-3xl font-bold text-white tracking-tight drop-shadow-lg">Ultimate Tracker</span>
                </Link>
                
                <h2 v-if="title" class="text-xl font-semibold text-white text-center">
                    {{ title }}
                </h2>
                <p v-if="description" class="text-sm text-gray-300 mt-2 text-center">
                    {{ description }}
                </p>
            </div>
            
            <slot />
            
        </div>

        <div class="relative z-20 w-full text-center mt-8 pb-4 px-4">
            <p class="text-xs text-gray-400/80 drop-shadow-md">
                Les images d'illustration appartiennent à leurs créateurs et studios respectifs.
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