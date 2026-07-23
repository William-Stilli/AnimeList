<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    LayoutDashboard,
    Library,
    Search,
    Trophy,
    BarChart3,
    Users,
    CircleHelp
} from 'lucide-vue-next';

import { driver } from "driver.js";
import "driver.js/dist/driver.css";

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
        icon: LayoutDashboard,
        id: 'tour-dashboard'
    },
    {
        title: 'Library',
        href: route('library'),
        icon: Library,
        id: 'tour-library'
    },
    {
        title: 'Search',
        href: route('anime.search'),
        icon: Search,
        id: 'tour-search'
    },
    {
        title: 'Ranking',
        href: route('anime.ranking'),
        icon: Trophy,
        id: 'tour-ranking'
    },
    {
        title: 'Stats',
        href: route('stats'),
        icon: BarChart3,
        id: 'tour-stats'
    },
    {
        title: 'Other users lists',
        href: route('community.index'),
        icon: Users,
        id: 'tour-community'
    },
];

const mangaNavItems: NavItem[] = [
    {
        title: 'Manga Library',
        href: route('mangas.index'),
        icon: Library,
        id: 'tour-manga-library'
    },
    {
        title: 'Manga Search',
        href: route('mangas.search'),
        icon: Search,
        id: 'tour-manga-search'
    }
];

const startTutorial = () => {
    const driverObj = driver({
        showProgress: true,
        animate: true,
        popoverClass: 'driverjs-theme',
        nextBtnText: 'Suivant ➔',
        prevBtnText: '⬅ Précedent',
        doneBtnText: 'C\'est parti !',
        steps: [
            { 
                popover: { 
                    title: 'Bienvenue dans le tutoriel !', 
                    description: 'Voici comment utiliser l\'application.' 
                } 
            },
            { 
                element: '#tour-dashboard', 
                popover: { 
                    title: 'Quartier Général', 
                    description: 'Retrouve ici ton XP et tes badges.', 
                    side: "right", align: 'start' 
                } 
            },
            { 
                element: '#tour-library', 
                popover: { 
                    title: 'Ta Collection Anime', 
                    description: 'Le cœur du système. Range tes visionnages, de Fate à Violet Evergarden, et gère ce que tu as vu, arrêté etc.', 
                    side: "right", align: 'start' 
                } 
            },
            { 
                element: '#tour-search', 
                popover: { 
                    title: 'Recherche Anime', 
                    description: 'Trouve facilement ton prochain anime préféré parmi des milliers de titres.', 
                    side: "right", align: 'start' 
                } 
            },
            { 
                element: '#tour-ranking', 
                popover: { 
                    title: 'Le classement', 
                    description: 'Range tes animes préférés dans avec un sistème de tier list.', 
                    side: "right", align: 'start' 
                } 
            },
            { 
                element: '#tour-stats', 
                popover: { 
                    title: 'Les Stats', 
                    description: 'Découvre les chiffres concernant tes animes qui parlent d\'eux-mêmes.', 
                    side: "right", align: 'start' 
                } 
            },
            { 
                element: '#tour-community', 
                popover: { 
                    title: 'La Communauté', 
                    description: 'Découvre les listes d\'autres utilisateurs', 
                    side: "right", align: 'start' 
                } 
            },
            { 
                element: '#tour-manga-library', 
                popover: { 
                    title: 'Ta Bibliothèque Manga', 
                    description: 'Gère ta collection de tomes physiques et garde une trace de tes chapitres lus.', 
                    side: "right", align: 'start' 
                } 
            },
            { 
                element: '#tour-manga-search', 
                popover: { 
                    title: 'Recherche Manga', 
                    description: 'Explore la base de données Tenrai pour trouver tes prochaines lectures.', 
                    side: "right", align: 'start' 
                } 
            }
        ]
    });

    driverObj.drive();
};
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
            <div class="px-4 py-2 mt-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                Anime
            </div>
            <NavMain :items="mainNavItems" />

            <div class="px-4 py-2 mt-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                Manga
            </div>
            <NavMain :items="mangaNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton @click="startTutorial" class="text-blue-500 hover:text-blue-400 hover:bg-blue-500/10 font-bold transition-colors">
                        <CircleHelp class="w-4 h-4 mr-2" />
                        <span>Tutoriel Interactif</span>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>

            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>