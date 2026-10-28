import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

function enableTestimonials() {
    document.querySelectorAll('[data-expand-testimonial]').forEach((button) => {
        const quote = document.getElementById(button.getAttribute('aria-controls'));
        if (!quote) return;
        quote.classList.add('is-collapsed');
        const measure = () => {
            if (button.getAttribute('aria-expanded') === 'true') return;
            button.hidden = quote.scrollHeight <= quote.clientHeight + 1;
        };
        if ('ResizeObserver' in window) new ResizeObserver(measure).observe(quote);
        measure();
        button.addEventListener('click', () => {
            const expanded = button.getAttribute('aria-expanded') !== 'true';
            quote.classList.toggle('is-collapsed', !expanded);
            button.setAttribute('aria-expanded', String(expanded));
            button.textContent = expanded ? button.dataset.less : button.dataset.more;
        });
    });
}

function enableProviderLocationDisclosure() {
    document.querySelectorAll('[data-provider-location]').forEach((details) => {
        const loadMap = () => {
            if (!details.open) return;
            const frame = details.querySelector('iframe[data-map-src]');
            if (frame && !frame.hasAttribute('src')) {
                frame.src = frame.dataset.mapSrc;
            }
        };
        details.addEventListener('toggle', loadMap);
        loadMap();
    });
}

function enableProviderAddressCopy() {
    const button = document.querySelector('[data-copy-provider-address]');
    const address = document.getElementById('provider-public-address');
    if (!button || !address) return;

    const label = button.querySelector('[data-copy-label]');
    const originalLabel = label.textContent;
    const status = document.querySelector('[data-address-copy-status]');
    const fallback = document.getElementById('provider-address-fallback');
    let resetTimer;
    button.hidden = false;

    button.addEventListener('click', async () => {
        clearTimeout(resetTimer);
        try {
            await navigator.clipboard.writeText(address.textContent.trim());
            label.textContent = button.dataset.copied;
            status.textContent = button.dataset.copied;
            fallback.hidden = true;
            resetTimer = setTimeout(() => {
                label.textContent = originalLabel;
                status.textContent = '';
            }, 3000);
        } catch {
            label.textContent = originalLabel;
            fallback.hidden = false;
            fallback.focus();
            fallback.select();
            status.textContent = button.dataset.copyFallback;
        }
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', enableTestimonials, { once: true });
    document.addEventListener('DOMContentLoaded', enableProviderAddressCopy, { once: true });
    document.addEventListener('DOMContentLoaded', enableProviderLocationDisclosure, { once: true });
} else {
    enableTestimonials();
    enableProviderAddressCopy();
    enableProviderLocationDisclosure();
}
