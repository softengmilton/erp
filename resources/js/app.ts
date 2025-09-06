import '../css/app.css';
import 'flowbite';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import { createApp, h } from 'vue';
import { ZiggyVue } from 'ziggy-js';
import { initializeTheme } from './composables/useAppearance';
import { CkeditorPlugin } from '@ckeditor/ckeditor5-vue'; // ✅ Correct - this is the plugin
import VueApexCharts from "vue3-apexcharts"; // ✅ import ApexCharts




const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) => resolvePageComponent(`./pages/${name}.vue`, import.meta.glob<DefineComponent>('./pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        const app = createApp({
            render: () => h(App as any, props) // 👈 cast fixes TS error
        });

        app.use(plugin);
        app.use(ZiggyVue);
        app.use(VueApexCharts);
        app.use(CkeditorPlugin);

        app.mount(el);
    },

    progress: {
        color: '#4B5563',
    },
});

// This will set light / dark mode on page load...
initializeTheme();
