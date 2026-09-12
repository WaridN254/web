<script>
    (function () {
        if (window.__toastListenerRegistered) return;
        window.__toastListenerRegistered = true;

        function registerToastListener() {
            if (!window.Livewire) return false;

            Livewire.on('toast', function (data) {
                if (Array.isArray(data)) data = data[0];
                if (!data) return;

                // Retry up to 500ms if HalisToast isn't ready yet
                function fire() {
                    if (!window.HalisToast) {
                        setTimeout(fire, 50);
                        return;
                    }
                    var method = data.type || 'show';
                    if (typeof window.HalisToast[method] !== 'function') {
                        method = 'show';
                    }
                    var opts = { title: data.title || '' };
                    if (data.description) opts.description = data.description;
                    if (data.duration != null) opts.duration = data.duration;
                    if (data.id) opts.id = data.id;
                    if (data.button) {
                        opts.button = {
                            title: data.button.title || 'OK',
                            onClick: function () {
                                if (data.button.url) {
                                    window.location.href = data.button.url;
                                } else if (data.button.event) {
                                    Livewire.dispatch(data.button.event, data.button.params || {});
                                } else if (data.button.js) {
                                    eval(data.button.js);
                                }
                            },
                        };
                    }
                    window.HalisToast[method](opts);
                }

                fire();
            });

            return true;
        }

        // Try registering immediately (Livewire may already be initialized)
        if (!registerToastListener()) {
            // If Livewire isn't ready, wait for livewire:init
            document.addEventListener('livewire:init', function () {
                registerToastListener();
            });
        }
    })();
</script>
