{{-- PWA Meta Tags --}}
<meta name="theme-color" content="#6366f1">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="HALIS">
<meta name="application-name" content="HALIS">
<meta name="msapplication-TileColor" content="#6366f1">
<meta name="msapplication-TileImage" content="/images/pwa/icon-144.png">
<link rel="manifest" href="/manifest.json">
<link rel="icon" type="image/svg+xml" href="/images/pwa/icon.svg">
<link rel="apple-touch-icon" href="/images/pwa/icon-192.png">

<script>
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js').then(reg => {
            console.log('SW registered:', reg.scope);
        }).catch(err => {
            console.log('SW registration failed:', err);
        });
    });
}
</script>
