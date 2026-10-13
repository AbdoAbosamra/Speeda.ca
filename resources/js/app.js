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

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', enableTestimonials, { once: true });
} else {
    enableTestimonials();
}
