{{-- Shared Local Offers behaviour: carousel and impression tracking. --}}
@once
@push('scripts')
<script>
(function () {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const trackUrl = @json(route('offers.track'));

    // Fire-and-forget: analytics must never get in a visitor's way.
    window.speedaOfferTrack = function (event, ids, source) {
        if (!ids || !ids.length) return;
        try {
            fetch(trackUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
                body: JSON.stringify({ event: event, ids: ids, source: source }),
                keepalive: true,
                credentials: 'same-origin'
            }).catch(function () {});
        } catch (e) {}
    };

    // A card counts as seen once half of it has been on screen.
    function watchImpressions(scope) {
        const source = scope.getAttribute('data-so-impressions');
        const seen = new Set();
        let queue = [];
        let timer = null;
        const flush = function () {
            if (queue.length) { window.speedaOfferTrack('impression', queue.splice(0, 24), source); }
            timer = null;
        };

        if (!('IntersectionObserver' in window)) return;
        const io = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (!entry.isIntersecting) return;
                const id = parseInt(entry.target.getAttribute('data-offer-id'), 10);
                if (!id || seen.has(id)) return;
                seen.add(id);
                queue.push(id);
                io.unobserve(entry.target);
                clearTimeout(timer);
                timer = setTimeout(flush, 1200);
            });
        }, { threshold: 0.5 });

        scope.querySelectorAll('[data-offer-id]').forEach(function (card) { io.observe(card); });
        window.addEventListener('pagehide', flush);
    }

    function initCarousel(root) {
        const track = root.querySelector('.so-track');
        const prev = root.querySelector('.so-arrow-prev');
        const next = root.querySelector('.so-arrow-next');
        const pager = root.parentElement.querySelector('.so-pager');
        if (!track) return;
        const rtl = getComputedStyle(track).direction === 'rtl';
        const gap = 18;

        const cardWidth = function () { const c = track.firstElementChild; return c ? c.getBoundingClientRect().width + gap : 300; };
        const perView = function () { return Math.max(1, Math.round((track.clientWidth + gap) / cardWidth())); };
        const pageWidth = function () { return cardWidth() * perView(); };
        const position = function () { return Math.abs(track.scrollLeft); };
        const pages = function () { return Math.max(1, Math.ceil(track.children.length / perView())); };

        function buildPager() {
            if (!pager) return;
            const n = pages();
            pager.innerHTML = '';
            pager.hidden = n < 2;
            for (let i = 0; i < n; i++) {
                const b = document.createElement('button');
                b.type = 'button';
                b.setAttribute('aria-label', pager.getAttribute('data-label').replace(':page', i + 1));
                b.addEventListener('click', function () { track.scrollTo({ left: (rtl ? -1 : 1) * i * pageWidth() }); });
                pager.appendChild(b);
            }
            sync();
        }

        function sync() {
            const max = track.scrollWidth - track.clientWidth - 2;
            if (prev) prev.disabled = position() <= 2;
            if (next) next.disabled = position() >= max;
            if (prev && next) { const hide = max <= 2; prev.hidden = hide; next.hidden = hide; }
            if (pager) {
                const current = position() >= max ? pager.children.length - 1 : Math.round(position() / pageWidth());
                Array.from(pager.children).forEach(function (b, i) {
                    b.classList.toggle('is-active', i === current);
                    b.setAttribute('aria-current', i === current ? 'true' : 'false');
                });
            }
        }

        if (prev) prev.addEventListener('click', function () { track.scrollBy({ left: (rtl ? 1 : -1) * pageWidth() }); });
        if (next) next.addEventListener('click', function () { track.scrollBy({ left: (rtl ? -1 : 1) * pageWidth() }); });
        track.addEventListener('scroll', function () { window.requestAnimationFrame(sync); }, { passive: true });
        window.addEventListener('resize', buildPager);
        buildPager();
    }

    function boot() {
        document.querySelectorAll('[data-so-carousel]').forEach(initCarousel);
        document.querySelectorAll('[data-so-impressions]').forEach(watchImpressions);
    }

    if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', boot); } else { boot(); }
})();
</script>
@endpush
@endonce
