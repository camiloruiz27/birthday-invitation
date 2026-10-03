import './bootstrap';
import '../css/app.css';

import { createRoot } from 'react-dom/client';
import { createInertiaApp, router } from '@inertiajs/react';
import route from 'ziggy-js';
import CookieConsent from './components/public/CookieConsent';
import { initConsent, trackPageView } from './lib/consent';

createInertiaApp({
    resolve: (name) => {
        const pages = import.meta.glob('./Pages/**/*.jsx', { eager: true });
        return pages[`./Pages/${name}.jsx`];
    },
    setup({ el, App, props }) {
        window.route = (name, params, absolute) =>
            route(name, params, absolute, window.Ziggy);

        // Analytics starts only if this visitor already accepted it on an
        // earlier visit; otherwise nothing is loaded (see lib/consent.js).
        initConsent();

        // Inertia navigations never reload the page, so GA is told about
        // each one explicitly — with the token-free URL. A no-op while
        // analytics is off.
        router.on('navigate', () => trackPageView());

        createRoot(el).render(
            <>
                <App {...props} />
                <CookieConsent />
            </>
        );
    },
});
