import '../css/app.css';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h, DefineComponent } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';

import { initializeTheme } from './composables/useAppearance';

import Toast from "vue-toastification";
import "vue-toastification/dist/index.css";

const appName = import.meta.env.VITE_APP_NAME || 'StuAnimeList';

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) =>
        resolvePageComponent(
            `./pages/${name}.vue`,
            import.meta.glob<DefineComponent>('./pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .use(Toast, {
                position: "top-center",
                timeout: 3000,
                closeOnClick: false,
                pauseOnFocusLoss: true,
                pauseOnHover: true,
                draggable: true,
                draggablePercent: 0.6,
                showCloseButtonOnHover: false,
                hideProgressBar: false,
                closeButton: "button",
                icon: true,
                rtl: false,
                transition: "Vue-Toastification__fade",
                maxToasts: 10,
                newestOnTop: true,
            })
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});

initializeTheme();