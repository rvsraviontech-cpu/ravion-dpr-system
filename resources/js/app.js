import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

/*
|--------------------------------------------------------------------------
| Ravion DPR PWA
|--------------------------------------------------------------------------
|
| Register the Ravion DPR service worker.
|
*/

if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker
            .register('/sw.js')
            .then(registration => {
                console.log(
                    'Ravion DPR service worker registered:',
                    registration.scope
                );
            })
            .catch(error => {
                console.error(
                    'Ravion DPR service worker registration failed:',
                    error
                );
            });
    });
}