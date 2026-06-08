(function () {
    'use strict';

    var scriptEl = document.currentScript;
    if (!scriptEl) {
        return;
    }

    var settings;
    try {
        settings = {
            firebaseConfig:   JSON.parse(scriptEl.getAttribute('data-firebase-config') || '{}'),
            vapidKey:         scriptEl.getAttribute('data-vapid-key') || '',
            serviceWorkerUrl: scriptEl.getAttribute('data-sw-url') || '',
            saveTokenUrl:     scriptEl.getAttribute('data-save-token-url') || ''
        };
    } catch (e) {
        console.warn('[WebPush] invalid firebase config', e);
        return;
    }

    if (!settings.firebaseConfig.apiKey || !settings.serviceWorkerUrl || !settings.saveTokenUrl) {
        return;
    }
    if (!('serviceWorker' in navigator) || !('Notification' in window)) {
        return;
    }

    function getFormKey() {
        if (window.FORM_KEY) {
            return window.FORM_KEY;
        }
        var input = document.querySelector('input[name="form_key"]');
        if (input && input.value) {
            return input.value;
        }
        var match = document.cookie.match(/(?:^|;\s*)form_key=([^;]+)/);
        return match ? decodeURIComponent(match[1]) : '';
    }

    function saveToken(token) {
        var data = new FormData();
        data.append('token', token);
        data.append('device_type', 'web');
        var fk = getFormKey();
        if (fk) {
            data.append('form_key', fk);
        }
        return fetch(settings.saveTokenUrl, {
            method: 'POST',
            body: data,
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        }).catch(function (err) {
            console.warn('[WebPush] save token failed', err);
        });
    }

    function initMessaging(registration) {
        if (typeof firebase === 'undefined' || !firebase.messaging) {
            console.warn('[WebPush] firebase SDK not loaded');
            return;
        }
        try {
            firebase.initializeApp(settings.firebaseConfig);
        } catch (e) { /* already initialised */ }

        var messaging = firebase.messaging();

        messaging.getToken({
            vapidKey: settings.vapidKey,
            serviceWorkerRegistration: registration
        }).then(function (token) {
            if (token) {
                console.log('[WebPush] FCM token:', token);
                saveToken(token);
            } else {
                console.log('[WebPush] no registration token available');
            }
        }).catch(function (err) {
            console.warn('[WebPush] getToken error:', err);
        });

        messaging.onMessage(function (payload) {
            if (Notification.permission !== 'granted') {
                return;
            }
            var d = (payload && payload.data) || {};
            var n = (payload && payload.notification) || {};
            var title = d.title || n.title;
            if (!title) {
                return;
            }
            var clickUrl = d.click_action || d.url || n.click_action || '/';
            var opts = {
                body: d.body || n.body || '',
                icon: d.icon || n.icon || '/favicon.ico',
                badge: d.badge || '/favicon.ico',
                tag: d.tag || 'webpush',
                renotify: true,
                data: { click_action: clickUrl }
            };
            var image = d.image || n.image;
            if (image) {
                opts.image = image;
            }
            registration.showNotification(title, opts);
        });
    }

    function registerSW() {
        navigator.serviceWorker
            .register(settings.serviceWorkerUrl, { scope: '/' })
            .then(function (registration) {
                return navigator.serviceWorker.ready.then(function () {
                    return registration;
                });
            })
            .then(initMessaging)
            .catch(function (err) {
                console.warn('[WebPush] SW registration failed:', err);
            });
    }

    function requestPermissionAndRegister() {
        console.log('[WebPush] Notification.permission =', Notification.permission);
        if (Notification.permission === 'granted') {
            registerSW();
            return;
        }
        if (Notification.permission === 'denied') {
            console.warn('[WebPush] Notifications are BLOCKED for this origin. '
                + 'Reset in chrome://settings/content/notifications (or click the lock icon > Site settings > Notifications > Ask/Allow), then reload.');
            return;
        }
        Notification.requestPermission().then(function (perm) {
            console.log('[WebPush] requestPermission ->', perm);
            if (perm === 'granted') {
                registerSW();
            } else if (perm === 'denied') {
                console.warn('[WebPush] User denied notification permission.');
            }
        }).catch(function (err) {
            console.warn('[WebPush] requestPermission error:', err);
        });
    }

    if (document.readyState === 'complete') {
        requestPermissionAndRegister();
    } else {
        window.addEventListener('load', requestPermissionAndRegister);
    }
})();
