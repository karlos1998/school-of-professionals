import '../css/app.css';
import '@mdi/font/css/materialdesignicons.css';
import 'vuetify/styles';

import { createInertiaApp } from '@inertiajs/vue3';
import { createPinia } from 'pinia';
import piniaPluginPersistedstate from 'pinia-plugin-persistedstate';
import type { DefineComponent } from 'vue';
import { createApp, h } from 'vue';
import { createVuetify } from 'vuetify';
import * as components from 'vuetify/components';
import * as directives from 'vuetify/directives';

const vuetify = createVuetify({
    components,
    directives,
    theme: {
        defaultTheme: 'school',
        themes: {
            school: {
                dark: false,
                colors: {
                    background: '#f4f8ff',
                    surface: '#ffffff',
                    primary: '#0f4c81',
                    secondary: '#f8b400',
                    info: '#2f7fc4',
                    success: '#1e824c',
                    warning: '#f2a900',
                    error: '#b42318',
                },
            },
        },
    },
});

createInertiaApp({
    progress: {
        color: '#f8b400',
    },
    resolve: (name) => {
        const pages = import.meta.glob<{ default: DefineComponent }>('./pages/**/*.vue');
        const page = pages[`./pages/${name}.vue`];

        if (!page) {
            throw new Error(`Page not found: ${name}`);
        }

        return page() as unknown as Promise<DefineComponent>;
    },
    setup({ el, App, props, plugin }) {
        const pinia = createPinia();
        pinia.use(piniaPluginPersistedstate);

        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(pinia)
            .use(vuetify)
            .mount(el);
    },
});
