document.addEventListener('DOMContentLoaded', function() {
    console.log('Home page loaded successfully');

    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const targetId = this.getAttribute('href');
            if(targetId === '#') return;

            const targetElement = document.querySelector(targetId);
            if(targetElement) {
                window.scrollTo({
                    top: targetElement.offsetTop - 80,
                    behavior: 'smooth'
                });
            }
        });
    });

    // Animate elements on scroll
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if(entry.isIntersecting) {
                entry.target.classList.add('animated');
            }
        });
    }, observerOptions);

    // Observe all benefit cards and step cards
    document.querySelectorAll('.benefit-card, .step-card, .benefit-large-card').forEach(el => {
        observer.observe(el);
    });
});

// Cookie banner functionality
document.addEventListener("DOMContentLoaded", function() {
    const banner = document.getElementById("cookie-banner");
    const overlay = document.getElementById("cookie-overlay");
    const acceptBtn = document.getElementById("accept-cookies");

    function showBanner() {
        if(banner && overlay) {
            banner.style.display = "block";
            overlay.style.display = "block";
            document.body.style.overflow = "hidden";
        }
    }

    function hideBanner() {
        if(banner && overlay) {
            banner.style.display = "none";
            overlay.style.display = "none";
            document.body.style.overflow = "auto";
        }
    }

    if (!localStorage.getItem("cookieConsentAccepted")) {
        setTimeout(showBanner, 1000);
    }

    if(acceptBtn) {
        acceptBtn.addEventListener("click", function() {
            localStorage.setItem("cookieConsentAccepted", "true");
            hideBanner();
        });
    }
});
