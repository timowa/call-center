import '../css/app.css';
import './bootstrap';
import 'noty/lib/noty.css';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import { createYmaps } from 'vue-yandex-maps';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

const ymaps = createYmaps({
    apikey: '10d91acc-0e3c-4917-96aa-d45353b77616',
});

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ymaps)
            .use(ZiggyVue)
            .mount(el);
    },
    progress: false,
});
