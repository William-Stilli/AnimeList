<template>
    <div class="group relative inline-block overflow-hidden rounded-lg cursor-pointer shadow-lg">
        <img :src="anime.image_url" :alt="anime.title"
            class="block w-full h-full aspect-[3/4] object-cover transition-transform duration-300 ease-in-out group-hover:scale-105" />
        <div
            class="absolute inset-0 z-10 flex items-center justify-center p-4 bg-black/75 opacity-0 transition-opacity duration-300 ease-in-out group-hover:opacity-100">
            <span class="text-white text-center font-bold text-sm lg:text-base">
                {{ anime.title }}
            </span>
        </div>
    </div>
</template>

<script setup>
defineProps({
    anime: {
        type: Object,
        required: true
    }
});
</script>