import './bootstrap';
import './client-home';
import Alpine from 'alpinejs';

// Performance optimization: defer Alpine initialization
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Alpine only after DOM is ready
    window.Alpine = Alpine;
    Alpine.start();
});

function isClientAppShell() {
    return document.body?.dataset?.clientApp === '1';
}

/**
 * Public marketing pages must not keep a controlling service worker.
 * An active SW (especially with clients.claim + HTML caching) commonly causes
 * "first click refreshes but doesn't navigate; second click works".
 */
async function clearPublicServiceWorkers() {
    if (!('serviceWorker' in navigator) || isClientAppShell()) {
        return;
    }

    try {
        const registrations = await navigator.serviceWorker.getRegistrations();
        await Promise.all(registrations.map((registration) => registration.unregister()));

        if (window.caches?.keys) {
            const keys = await caches.keys();
            await Promise.all(keys.map((key) => caches.delete(key)));
        }
    } catch (error) {
        console.warn('Failed to clear public service workers', error);
    }
}

// PWA: service worker + lightweight push-subscription bootstrap (client app only).
async function registerServiceWorker() {
    if (!('serviceWorker' in navigator) || !isClientAppShell()) {
        return;
    }

    try {
        await navigator.serviceWorker.register('/sw.js', { scope: '/' });
    } catch (error) {
        console.warn('Service worker registration failed', error);
    }
}

function urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
    const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
    const rawData = window.atob(base64);
    const outputArray = new Uint8Array(rawData.length);
    for (let i = 0; i < rawData.length; ++i) {
        outputArray[i] = rawData.charCodeAt(i);
    }
    return outputArray;
}

async function initPushSubscription() {
    if (!isClientAppShell()) {
        return;
    }

    if (!('serviceWorker' in navigator) || !('PushManager' in window) || !window.axios) {
        return;
    }

    const vapidPublicKey = document.querySelector('meta[name="vapid-public-key"]')?.content;
    if (!vapidPublicKey) {
        return;
    }

    const registration = await navigator.serviceWorker.ready;
    let subscription = await registration.pushManager.getSubscription();

    if (!subscription && Notification.permission === 'granted') {
        subscription = await registration.pushManager.subscribe({
            userVisibleOnly: true,
            applicationServerKey: urlBase64ToUint8Array(vapidPublicKey),
        });
    }

    if (!subscription) {
        return;
    }

    await window.axios.post('/api/v1/push-subscriptions', subscription.toJSON());
}

document.addEventListener('DOMContentLoaded', async function () {
    await clearPublicServiceWorkers();
    await registerServiceWorker();

    const shouldPromptPush = document.body.dataset.pushPrompt === '1' && isClientAppShell();
    if (shouldPromptPush && 'Notification' in window && Notification.permission === 'default') {
        const permission = await Notification.requestPermission();
        if (permission === 'granted') {
            await initPushSubscription();
        }
        return;
    }

    if (isClientAppShell() && 'Notification' in window && Notification.permission === 'granted') {
        await initPushSubscription();
    }
});

// Optimize scroll performance
let ticking = false;
function optimizeScroll() {
    if (!ticking) {
        requestAnimationFrame(() => {
            // Scroll optimizations can be added here
            ticking = false;
        });
        ticking = true;
    }
}

// Add passive scroll listener for better performance
window.addEventListener('scroll', optimizeScroll, { passive: true });

// Preload critical resources
document.addEventListener('DOMContentLoaded', function() {
    // Preload images that will be needed soon
    const criticalImages = document.querySelectorAll('img[loading="eager"]');
    criticalImages.forEach(img => {
        if (img.dataset.src) {
            img.src = img.dataset.src;
        }
    });
});
