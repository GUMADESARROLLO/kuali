import '../css/app.css';
import './bootstrap';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, DefineComponent, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import Swal from 'sweetalert2';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
    didOpen: (toast) => {
        toast.onmouseenter = Swal.stopTimer;
        toast.onmouseleave = Swal.resumeTimer;
    },
});

// Listen for Inertia page updates to show flash messages
import { router } from '@inertiajs/vue3';

router.on('success', (event: any) => {
    const flash = event.detail.page?.props?.flash ?? {};
    setTimeout(() => {
        if (flash.success) {
            Toast.fire({ icon: 'success', title: flash.success });
        } else if (flash.error) {
            Toast.fire({ icon: 'error', title: flash.error });
        } else if (flash.warning) {
            Toast.fire({ icon: 'warning', title: flash.warning });
        } else if (flash.info) {
            Toast.fire({ icon: 'info', title: flash.info });
        }
    }, 100);
});

// Helper for confirm dialogs
(window as any).confirmDialog = async (title: string, text: string): Promise<boolean> => {
    const result = await Swal.fire({
        title,
        text,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ba1a1a',
        cancelButtonColor: '#757681',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        reverseButtons: true,
    });
    return result.isConfirmed;
};

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
