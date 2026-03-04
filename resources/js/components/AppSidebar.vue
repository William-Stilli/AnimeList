<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    LayoutDashboard,
    Library,
    Search,
    Trophy,
    BarChart3,
    Users,
    BookOpen,
    Folder
} from 'lucide-vue-next';

import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { type NavItem } from '@/types';

import AppLogo from './AppLogo.vue';

const mainNavItems: NavItem[] = [
    {
        title: 'Anime Dashboard',
        href: route('anime.dashboard'),
        icon: LayoutDashboard
    },
    {
        title: 'Library',
        href: route('library'),
        icon: Library,
    },
    {
        title: 'Search',
        href: route('anime.search'),
        icon: Search,
    },
    {
        title: 'Ranking',
        href: route('anime.ranking'),
        icon: Trophy,
    },
    {
        title: 'Stats',
        href: route('stats'),
        icon: BarChart3,
    },
    {
        title: 'Other users lists',
        href: route('community.index'),
        icon: Users,
    },
];

</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link>
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
