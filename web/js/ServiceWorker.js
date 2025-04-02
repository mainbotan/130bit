
/*Service Worker*/
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('service-worker.js?v=8')
            .then((registration) => {
                self.addEventListener('install', (event) => {
                    // Ничего не кэшируем
                    event.waitUntil(Promise.resolve());
                });
                
                self.addEventListener('fetch', (event) => {
                    // Просто пропускаем все запросы без кэширования
                    event.respondWith(fetch(event.request));
                });
                
                self.addEventListener('activate', (event) => {
                    // Очищаем старые кэши, если они есть
                    event.waitUntil(
                        caches.keys().then((cacheNames) => {
                            return Promise.all(
                                cacheNames.map((cacheName) => {
                                    return caches.delete(cacheName);
                                })
                            );
                        })
                    );
                });
            })
            .catch((error) => {
                // console.error('Ошибка регистрации Service Worker:', error);
            });
    });
    // self.addEventListener('activate', (event) => {
    //     event.waitUntil(
    //       caches.keys().then((cacheNames) => {
    //         return Promise.all(
    //           cacheNames.map((cacheName) => {
    //             if (cacheName !== CACHE_NAME) {
    //               return caches.delete(cacheName);
    //             }
    //           })
    //         );
    //       })
    //     );
    //   });
}