
<?php if(config('facebook.enabled') && !request()->routeIs('admin.*')): ?>
    <!-- Meta Pixel Code -->
    <script>
        // Cookie consent hook: set window.fbPixelConsent = false before this script
        // loads to disable tracking until user grants consent.
        if (typeof window.fbPixelConsent === 'undefined') {
            window.fbPixelConsent = true;
        }

        if (window.fbPixelConsent) {
            !function (f, b, e, v, n, t, s) {
                if (f.fbq) return; n = f.fbq = function () {
                    n.callMethod ?
                        n.callMethod.apply(n, arguments) : n.queue.push(arguments)
                };
                if (!f._fbq) f._fbq = n; n.push = n; n.loaded = !0; n.version = '2.0';
                n.queue = []; t = b.createElement(e); t.async = !0;
                t.src = v; s = b.getElementsByTagName(e)[0];
                s.parentNode.insertBefore(t, s)
            }(window, document, 'script',
                'https://connect.facebook.net/en_US/fbevents.js');

            fbq('init', '<?php echo e(config("facebook.pixel_id")); ?>');
            fbq('track', 'PageView');
        }
    </script>
    <noscript>
        <img height="1" width="1" style="display:none"
            src="https://www.facebook.com/tr?id=<?php echo e(config('facebook.pixel_id')); ?>&ev=PageView&noscript=1" />
    </noscript>
    <!-- End Meta Pixel Code -->

    
    <?php if(session('meta_pixel_complete_registration')): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                if (typeof fbq === 'function') {
                    fbq('track', 'CompleteRegistration', {
                        status: true,
                        language: '<?php echo e(app()->getLocale()); ?>'
                    }, { eventID: '<?php echo e(session("meta_pixel_registration_event_id", "reg_" . time())); ?>' });
                }
            });
        </script>
    <?php endif; ?>
<?php endif; ?><?php /**PATH Y:\Speeda - Versions\Speeda\resources\views/partials/meta-pixel.blade.php ENDPATH**/ ?>