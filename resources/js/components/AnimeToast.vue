<script setup>
defineProps({
    title: String,
    image: String,
    message: String,
})
</script>

<template>
    <div class="flex items-center gap-3 min-w-[250px]">
        <img :src="image" alt="Cover" class="w-10 h-14 object-cover rounded shadow-sm border border-white/20"></img>

        <div class="flex flex-col">
            <span class="font-bold text-sm leading-tight">{{ title }}</span>
            <span class="text-xs opacity-90 mt-0.5">{{ message }}</span>
        </div>
    </div>
</template>