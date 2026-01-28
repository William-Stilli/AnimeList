<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { useInitials } from '@/composables/useInitials';

const page = usePage();

const user = computed(() => page.props.auth.user);

const { getInitials } = useInitials();
</script>

<template>
    <div class="flex items-center gap-3 text-left text-sm leading-tight">

        <Avatar class="h-8 w-8 rounded-lg">
            <AvatarImage :src="user.profile_photo_url || ''" :alt="user.name" />
            <AvatarFallback class="rounded-lg bg-sidebar-primary text-sidebar-primary-foreground">
                {{ getInitials(user.name) }}
            </AvatarFallback>
        </Avatar>

        <div class="grid flex-1 text-left text-sm leading-tight">
            <span class="truncate font-semibold text-gray-900 dark:text-gray-100">
                {{ user.name }}
            </span>

            <span class="truncate text-xs text-gray-500 dark:text-gray-400">
                {{ user.email }}
            </span>
        </div>

    </div>
</template>