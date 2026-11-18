import React from 'react';
import { createRoot } from 'react-dom/client';
import { createInertiaApp } from '@inertiajs/react';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import Sidebar from './Components/Sidebar';

const pages = import.meta.glob('./Pages/**/*.jsx');

createInertiaApp({
    resolve: async (name) => {
        const page = await resolvePageComponent(`./Pages/${name}.jsx`, pages);

        if (!['Ferreteria/Inventario', 'Welcome'].includes(name)) {
            return page;
        }

        const Page = page.default;

        return {
            default: (props) =>
                React.createElement(
                    'div',
                    { className: 'min-h-screen bg-slate-950 text-slate-100 lg:flex' },
                    React.createElement(Sidebar),
                    React.createElement(
                        'main',
                        { className: 'min-w-0 flex-1 lg:ml-64' },
                        React.createElement(Page, props),
                    ),
                ),
        };
    },
    setup({ el, App, props }) {
        createRoot(el).render(React.createElement(App, props));
    },
});
