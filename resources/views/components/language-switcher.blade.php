@php
    $currentLocale = app()->getLocale();
    $locales = [
        'en' => ['flag' => '🇺🇸', 'name' => __('language.english')],
        'ar' => ['flag' => '🇸🇦', 'name' => __('language.arabic')],
        'fr' => ['flag' => '🇫🇷', 'name' => __('language.french')]
    ];
@endphp

<div class="language-switcher" id="languageSwitcher">
    <button type="button" class="language-btn" onclick="toggleLanguageMenu()" aria-haspopup="true" aria-expanded="false">
        <span class="current-language">
            {{ $locales[$currentLocale]['flag'] }} {{ $locales[$currentLocale]['name'] }}
        </span>
        <i class="fas fa-chevron-down"></i>
    </button>

    <div class="language-dropdown" id="languageDropdown" role="menu">
        @foreach($locales as $code => $localeInfo)
            @if($code !== $currentLocale)
                <a href="#"
                   class="language-option"
                   role="menuitem"
                   data-locale="{{ $code }}"
                   onclick="event.preventDefault(); switchLanguage('{{ $code }}');">
                    <span class="language-flag">{{ $localeInfo['flag'] }}</span>
                    <span class="language-name">{{ $localeInfo['name'] }}</span>
                </a>
            @endif
        @endforeach
    </div>
</div>

<style>
.language-switcher {
    position: relative;
    z-index: 1000;
}

.language-btn {
    background: linear-gradient(145deg, #ffffff, #f8fafc);
    border: 2px solid var(--border-color, #e2e8f0);
    border-radius: 12px;
    padding: 0.75rem 1.25rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.95rem;
    font-weight: 600;
    transition: all 0.3s ease;
    color: var(--text-secondary, #64748b);
    min-width: 140px;
    justify-content: space-between;
}

.language-btn:hover {
    border-color: var(--primary-color, #3b82f6);
    background: linear-gradient(145deg, #f8fafc, #ffffff);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.language-dropdown {
    position: absolute;
    top: 100%;
    right: 0;
    background: white;
    border: 2px solid var(--border-color, #e2e8f0);
    border-radius: 12px;
    margin-top: 8px;
    min-width: 180px;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    display: none;
    overflow: hidden;
    z-index: 1001;
}

.language-dropdown.show {
    display: block;
    animation: dropdownSlide 0.2s ease-out;
}

@keyframes dropdownSlide {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

.language-option {
    width: 100%;
    padding: 0.875rem 1.25rem;
    background: white;
    border: none;
    border-bottom: 1px solid #f1f5f9;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 12px;
    transition: all 0.2s ease;
    text-align: left;
    font-size: 0.9rem;
    font-weight: 500;
    text-decoration: none;
    color: inherit;
}

.language-option:hover {
    background: linear-gradient(135deg, rgba(79, 70, 229, 0.05), rgba(6, 182, 212, 0.05));
    color: var(--primary-color, #3b82f6);
    padding-left: 1.5rem;
}

.language-option:last-child {
    border-bottom: none;
}

.language-flag {
    font-size: 18px;
    min-width: 24px;
}

.language-name {
    font-size: 0.9rem;
    font-weight: 500;
    flex: 1;
}

/* RTL Support */
[dir="rtl"] .language-dropdown {
    right: auto;
    left: 0;
}

[dir="rtl"] .language-option {
    text-align: right;
    flex-direction: row-reverse;
}

[dir="rtl"] .language-option:hover {
    padding-right: 1.5rem;
    padding-left: 1.25rem;
}

[dir="rtl"] .language-btn {
    flex-direction: row-reverse;
}
</style>

<script>
function toggleLanguageMenu() {
    const dropdown = document.getElementById('languageDropdown');
    const button = document.querySelector('#languageSwitcher .language-btn');
    dropdown.classList.toggle('show');
    if (button) {
        button.setAttribute('aria-expanded', dropdown.classList.contains('show') ? 'true' : 'false');
    }
}

// Function to switch language
function switchLanguage(locale) {
    const currentUrl = window.location.href;
    const btn = document.querySelector('.language-btn .current-language');

    // Show loading state
    if (btn) {
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> ' + btn.textContent;
    }

    // Create a form to POST the language change
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route("locale.update") }}';
    form.style.display = 'none';

    // Add CSRF token
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = '{{ csrf_token() }}';
    form.appendChild(csrfInput);

    // Add locale
    const localeInput = document.createElement('input');
    localeInput.type = 'hidden';
    localeInput.name = 'locale';
    localeInput.value = locale;
    form.appendChild(localeInput);

    // Add redirect URL
    const redirectInput = document.createElement('input');
    redirectInput.type = 'hidden';
    redirectInput.name = 'redirect_to';
    redirectInput.value = currentUrl;
    form.appendChild(redirectInput);

    document.body.appendChild(form);
    form.submit();
}

// إغلاق القائمة عند النقر خارجها
document.addEventListener('click', function(event) {
    const switcher = document.getElementById('languageSwitcher');
    const dropdown = document.getElementById('languageDropdown');

    if (switcher && !switcher.contains(event.target)) {
        dropdown.classList.remove('show');
        document.querySelector('#languageSwitcher .language-btn')?.setAttribute('aria-expanded', 'false');
    }
});

// إغلاق القائمة عند الضغط على ESC
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        const dropdown = document.getElementById('languageDropdown');
        dropdown.classList.remove('show');
        document.querySelector('#languageSwitcher .language-btn')?.setAttribute('aria-expanded', 'false');
    }
});
</script>
