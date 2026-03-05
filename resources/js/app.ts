import '../css/app.css';
import './bootstrap';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, DefineComponent, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';

const appName = import.meta.env.VITE_APP_NAME || 'MedidaTek';

const isLocalhost =
    typeof window !== 'undefined' &&
    ['localhost', '127.0.0.1'].includes(window.location.hostname);

if (isLocalhost && typeof window !== 'undefined' && 'serviceWorker' in navigator) {
    const key = 'sw_cleanup_done_v1';
    const already = window.sessionStorage.getItem(key) === '1';
    if (!already) {
        window.sessionStorage.setItem(key, '1');
        Promise.allSettled([
            navigator.serviceWorker
                .getRegistrations()
                .then((regs) => Promise.all(regs.map((r) => r.unregister()))),
            'caches' in window
                ? caches.keys().then((keys) => Promise.all(keys.map((k) => caches.delete(k))))
                : Promise.resolve(),
        ]).finally(() => {
            if (navigator.serviceWorker.controller) {
                window.location.reload();
            }
        });
    }
}

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob<DefineComponent>('./Pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});
