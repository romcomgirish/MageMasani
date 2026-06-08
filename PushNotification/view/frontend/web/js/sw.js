/* eslint-env serviceworker */
/* global firebase, FIREBASE_CONFIG, FIREBASE_APP_URL, FIREBASE_MESSAGING_URL */

importScripts(self.FIREBASE_APP_URL);
importScripts(self.FIREBASE_MESSAGING_URL);

firebase.initializeApp(self.FIREBASE_CONFIG);

var messaging = firebase.messaging();

function buildNotification(payload) {
    var d = (payload && payload.data) || {};
    var n = (payload && payload.notification) || {};
    var title = d.title || n.title || 'Notification';
    var clickUrl = d.click_action || d.url || n.click_action || '/';
    var options = {
        body: d.body || n.body || '',
        icon: d.icon || n.icon || '/favicon.ico',
        badge: d.badge || '/favicon.ico',
        tag: d.tag || 'webpush',
        renotify: true,
        requireInteraction: false,
        data: { click_action: clickUrl }
    };
    var image = d.image || n.image;
    if (image) {
        options.image = image;
    }
    return { title: title, options: options };
}

messaging.onBackgroundMessage(function (payload) {
    var n = buildNotification(payload);
    return self.registration.showNotification(n.title, n.options);
});

self.addEventListener('install', function (event) {
    event.waitUntil(self.skipWaiting());
});

self.addEventListener('activate', function (event) {
    event.waitUntil(self.clients.claim());
});

self.addEventListener('notificationclick', function (event) {
    event.notification.close();
    var url = (event.notification.data && event.notification.data.click_action) || '/';
    event.waitUntil(
        self.clients.matchAll({ type: 'window', includeUncontrolled: true }).then(function (list) {
            for (var i = 0; i < list.length; i++) {
                if (list[i].url === url && 'focus' in list[i]) {
                    return list[i].focus();
                }
            }
            if (self.clients.openWindow) {
                return self.clients.openWindow(url);
            }
        })
    );
});
