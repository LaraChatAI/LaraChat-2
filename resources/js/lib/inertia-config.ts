import { router } from '@inertiajs/vue3';

// Configure Inertia for smoother transitions
router.on('before', (event) => {
    // Only preserve scroll and state for GET requests (navigation)
    if (event.detail.visit.method === 'get') {
        event.detail.visit.preserveScroll = true;
        event.detail.visit.preserveState = true;
    }
});

// Cache page components
router.on('success', (event) => {
    // Cache successful page loads
    const url = event.detail.page.url;
    const component = event.detail.page.component;

    // Store in session storage for quick retrieval
    if (typeof window !== 'undefined' && window.sessionStorage) {
        const cache = {
            component,
            props: event.detail.page.props,
            timestamp: Date.now(),
        };

        try {
            sessionStorage.setItem(`inertia-cache-${url}`, JSON.stringify(cache));
        } catch {
            // Storage might be full, ignore
        }
    }
});

export const inertiaConfig = {
    // Default visit options
    visitOptions: {
        preserveScroll: true,
        preserveState: true,
    },
};
