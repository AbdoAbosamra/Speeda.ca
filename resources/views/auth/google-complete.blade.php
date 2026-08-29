{{--
    Step two of a Google signup, for either role.

    Deliberately a plain, self-contained page with native <select> elements
    rather than a copy of the registration form's custom dropdowns: this is a
    one-time screen, and native controls cannot get into a state where the
    visible label and the hidden value disagree.

    The provider fields are always in the markup and hidden with a class, not
    rendered conditionally, so switching role is instant and old() values
    survive a failed submit. Their `required` attributes are toggled with them
    - a hidden field the browser insists on would block the client submit with
    a validation bubble pointing at nothing visible.

    $pending           google_id / email / name / role, held in the session
    $professionGroups  terminal categories grouped by their root section
    $locations         active locations
--}}
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}"
    dir="{{ in_array(app()->getLocale(), ['ar', 'he', 'ur', 'fa']) ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <link rel="icon" type="image/png" href="{{ asset('images/New_logo.png') }}">
    <title>{{ __('auth.google_complete_title') }} | {{ config('app.name', 'Speeda') }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 32px 16px;
        }

        .card {
            width: 100%;
            max-width: 560px;
            background: #fff;
            border-radius: 24px;
            padding: 40px;
            box-shadow: 0 24px 64px rgba(15, 31, 61, .22);
        }

        .brand { text-align: center; margin-bottom: 24px; }
        .brand-logo { height: 52px; }

        h1 { font-size: 1.5rem; font-weight: 700; color: #1f2937; margin: 0 0 8px; text-align: center; }
        .intro { color: #6b7280; text-align: center; margin: 0 0 24px; line-height: 1.6; font-size: .95rem; }

        .identity {
            display: flex;
            align-items: center;
            gap: 12px;
            background: #f8faff;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 14px 16px;
            margin-bottom: 24px;
        }

        .identity i { color: #ea4335; font-size: 1.25rem; }
        .identity-label { font-size: .75rem; color: #9ca3af; text-transform: uppercase; letter-spacing: .5px; }
        .identity-email { font-weight: 600; color: #1f2937; word-break: break-all; }

        .form-group { margin-bottom: 18px; }

        .roles { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 22px; }

        .role-card {
            position: relative;
            display: flex;
            flex-direction: column;
            gap: 4px;
            padding: 16px 14px;
            border: 2px solid #e5e7eb;
            border-radius: 14px;
            cursor: pointer;
            text-align: center;
            transition: border-color .2s, background .2s, transform .2s;
        }

        .role-card input { position: absolute; opacity: 0; pointer-events: none; }
        .role-card i { font-size: 1.35rem; color: #667eea; margin-bottom: 2px; }
        .role-card .role-title { font-weight: 600; color: #1f2937; font-size: .95rem; }
        .role-card .role-hint { font-size: .78rem; color: #9ca3af; line-height: 1.4; }
        .role-card:hover { border-color: #c7d2fe; }

        .role-card:has(input:checked) {
            border-color: #667eea;
            background: #f5f7ff;
            transform: translateY(-1px);
        }

        .role-card:has(input:focus-visible) { box-shadow: 0 0 0 4px rgba(102, 126, 234, .12); }

        .provider-only[hidden] { display: none; }

        .form-label {
            display: block;
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
            font-size: .9rem;
        }

        .form-label i { color: #667eea; margin-inline-end: 6px; }
        .req { color: #ef4444; }
        .opt { color: #9ca3af; font-weight: 400; font-size: .8rem; }

        .form-input, .form-select, .select-trigger {
            width: 100%;
            padding: 13px 16px;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            font-family: inherit;
            font-size: .95rem;
            color: #1f2937;
            background: #fff;
            transition: border-color .2s, box-shadow .2s;
        }

        .form-input:focus, .form-select:focus, .select-trigger:focus-visible {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, .12);
        }

        .form-input.has-error, .form-select.has-error, .select-trigger.has-error { border-color: #ef4444; }

        .custom-select { position: relative; }

        .select-trigger {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            text-align: start;
            cursor: pointer;
        }

        .select-value.placeholder { color: #9ca3af; }

        .select-arrow {
            width: 9px;
            height: 9px;
            border-inline-end: 2px solid #667eea;
            border-bottom: 2px solid #667eea;
            transform: rotate(45deg);
            transition: transform .2s;
            flex-shrink: 0;
        }

        .select-trigger.active .select-arrow { transform: rotate(225deg); }

        .select-options {
            position: absolute;
            z-index: 20;
            inset-inline: 0;
            top: calc(100% + 6px);
            max-height: 280px;
            overflow-y: auto;
            display: none;
            background: #fff;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            box-shadow: 0 16px 36px rgba(15, 31, 61, .16);
        }

        .select-options.show { display: block; }

        .select-option {
            padding: 11px 16px;
            color: #374151;
            cursor: pointer;
            transition: background .15s, color .15s;
        }

        .select-option:hover, .select-option.selected {
            background: #f5f7ff;
            color: #4f46e5;
        }

        .select-group-header {
            padding: 9px 16px;
            border-top: 1px solid #e5e7eb;
            background: #f8faff;
            color: #667eea;
            font-size: .78rem;
            font-weight: 700;
            text-transform: uppercase;
        }

        .select-option.grouped-option { padding-inline-start: 24px; }

        .phone-shell {
            display: flex;
            align-items: stretch;
            gap: 10px;
        }

        .country-badge {
            min-width: 104px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            background: #f8faff;
            color: #374151;
            font-weight: 600;
        }

        .country-badge .country-name { color: #9ca3af; font-size: .75rem; }
        .phone-field { min-width: 0; flex: 1; }

        .phone-hint, .phone-valid {
            display: flex;
            align-items: center;
            gap: 6px;
            margin-top: 7px;
            color: #6b7280;
            font-size: .78rem;
        }

        .phone-valid { color: #15803d; }
        .phone-valid[hidden] { display: none; }

        .field-error {
            display: flex;
            align-items: center;
            gap: 6px;
            color: #ef4444;
            font-size: .82rem;
            margin-top: 6px;
        }

        .terms {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin: 22px 0;
            font-size: .88rem;
            color: #4b5563;
            line-height: 1.6;
        }

        .terms input { margin-top: 3px; width: 18px; height: 18px; accent-color: #667eea; flex-shrink: 0; }

        .submit {
            width: 100%;
            padding: 15px;
            border: 0;
            border-radius: 12px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: #fff;
            font-family: inherit;
            font-weight: 700;
            font-size: 1rem;
            cursor: pointer;
            transition: transform .2s, box-shadow .2s;
        }

        .submit:hover { transform: translateY(-2px); box-shadow: 0 10px 24px rgba(102, 126, 234, .35); }

        .bail { display: block; text-align: center; margin-top: 18px; color: #9ca3af; font-size: .85rem; }

        .alert {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #b91c1c;
            border-radius: 12px;
            padding: 12px 16px;
            margin-bottom: 20px;
            font-size: .9rem;
        }

        @media (max-width: 520px) {
            .card { padding: 28px 22px; border-radius: 18px; }
            .phone-shell { gap: 8px; }
            .country-badge { min-width: 88px; }
        }
    </style>
</head>

<body>
    <div class="card">
        <div class="brand">
            <img src="{{ asset('images/New_logo.png') }}" alt="{{ config('app.name', 'Speeda') }}" class="brand-logo">
        </div>

        <h1>{{ __('auth.google_complete_title') }}</h1>
        <p class="intro">{{ __('auth.google_complete_intro') }}</p>

        @if (session('error'))
            <div class="alert">{{ session('error') }}</div>
        @endif

        <div class="identity">
            <i class="fab fa-google"></i>
            <div>
                <div class="identity-label">{{ __('auth.google_signed_in_as') }}</div>
                <div class="identity-email">{{ $pending['email'] }}</div>
            </div>
        </div>

        <form method="POST" action="{{ route('google.complete.store') }}" id="google-complete-form">
            @csrf

            @php($selectedRole = old('role', $pending['role'] ?? 'client'))
            @php($isProvider = $selectedRole === 'service_provider')

            <div class="form-group">
                <span class="form-label">
                    <i class="fas fa-id-badge"></i>{{ __('auth.google_role_question') }} <span class="req">*</span>
                </span>
                <div class="roles">
                    <label class="role-card">
                        <input type="radio" name="role" value="client" @checked($selectedRole !== 'service_provider')>
                        <i class="fas fa-user"></i>
                        <span class="role-title">{{ __('auth.client') }}</span>
                        <span class="role-hint">{{ __('auth.google_role_client_hint') }}</span>
                    </label>
                    <label class="role-card">
                        <input type="radio" name="role" value="service_provider" @checked($selectedRole === 'service_provider')>
                        <i class="fas fa-briefcase"></i>
                        <span class="role-title">{{ __('auth.service_provider') }}</span>
                        <span class="role-hint">{{ __('auth.google_role_provider_hint') }}</span>
                    </label>
                </div>
                @error('role')
                    <div class="field-error"><i class="fas fa-exclamation-circle"></i>{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="name" class="form-label">
                    <i class="fas fa-user"></i>{{ __('auth.full_name') }} <span class="req">*</span>
                </label>
                <input id="name" name="name" type="text" class="form-input @error('name') has-error @enderror"
                    value="{{ old('name', $pending['name']) }}" required autocomplete="name">
                @error('name')
                    <div class="field-error"><i class="fas fa-exclamation-circle"></i>{{ $message }}</div>
                @enderror
            </div>

            <div class="provider-only" id="provider-fields" @if($selectedRole !== 'service_provider') hidden @endif>
                <div class="form-group">
                    <label for="profession-trigger" class="form-label">
                        <i class="fas fa-briefcase"></i>{{ __('auth.profession') }} <span class="req">*</span>
                    </label>
                    <div class="custom-select" id="profession-select">
                        <button type="button" class="select-trigger @error('profession') has-error @enderror"
                            id="profession-trigger" aria-expanded="false">
                            <span class="select-value placeholder" id="profession-value">{{ __('auth.select_profession') }}</span>
                            <span class="select-arrow"></span>
                        </button>
                        <div class="select-options" id="profession-options">
                            @include('auth.partials.profession-options')
                        </div>
                        <input type="hidden" name="profession" id="profession-input" value="{{ old('profession') }}"
                            @disabled(!$isProvider)>
                    </div>
                    <div class="field-error" id="profession-error">
                        @error('profession')<i class="fas fa-exclamation-circle"></i>{{ $message }}@enderror
                    </div>
                </div>

                <div class="form-group">
                    <label for="city-trigger" class="form-label">
                        <i class="fas fa-location-dot"></i>{{ __('auth.city') }} <span class="req">*</span>
                    </label>
                    <div class="custom-select" id="city-select">
                        <button type="button" class="select-trigger @error('city') has-error @enderror"
                            id="city-trigger" aria-expanded="false">
                            <span class="select-value placeholder" id="city-value">{{ __('auth.select_city') }}</span>
                            <span class="select-arrow"></span>
                        </button>
                        <div class="select-options" id="city-options">
                            @include('auth.partials.signup-city-options')
                        </div>
                        <input type="hidden" name="city" id="city-input" value="{{ old('city') }}"
                            @disabled(!$isProvider)>
                    </div>
                    <div class="field-error" id="city-error">
                        @error('city')<i class="fas fa-exclamation-circle"></i>{{ $message }}@enderror
                    </div>
                </div>

                <div class="form-group">
                    <label for="mobile" class="form-label">
                        <i class="fas fa-mobile-alt"></i>{{ __('auth.mobile_number') }} <span class="req">*</span>
                    </label>
                    <div class="phone-shell">
                        <div class="country-badge">
                            <span>CA</span><span>+1</span>
                            <input type="hidden" name="mobile_country_code" value="+1" @disabled(!$isProvider)>
                        </div>
                        <div class="phone-field">
                            <input id="mobile" name="mobile" type="tel"
                                class="form-input @error('mobile') has-error @enderror"
                                value="{{ old('mobile') }}" placeholder="613-520-4877" autocomplete="tel"
                                inputmode="numeric" pattern="[0-9\-\s]{10,20}" minlength="10" maxlength="15"
                                aria-describedby="mobile-error phone-format" @required($isProvider) @disabled(!$isProvider)>
                            <div class="phone-hint" id="phone-hint">
                                <i class="fas fa-lightbulb"></i>
                                <span>{{ __('auth.phone_auto_format') ?? 'Type your Canadian phone number without the country code' }}</span>
                            </div>
                            <div class="phone-valid" id="phone-format" hidden>
                                <i class="fas fa-check-circle"></i>
                                <span>{{ __('validation.valid_canadian_format') ?? 'Valid Canadian number' }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="field-error" id="mobile-error">
                        @error('mobile')<i class="fas fa-exclamation-circle"></i>{{ $message }}@enderror
                    </div>
                </div>

                <div class="form-group">
                    <label for="whatsapp_number" class="form-label">
                        <i class="fab fa-whatsapp"></i>{{ __('service_provider.whatsapp_number') }}
                        <span class="opt">({{ __('auth.optional') }})</span>
                    </label>
                    <div class="phone-shell">
                        <div class="country-badge">
                            <span>CA</span><span>+1</span>
                            <input type="hidden" name="whatsapp_country_code" value="+1" @disabled(!$isProvider)>
                        </div>
                        <div class="phone-field">
                            <input id="whatsapp_number" name="whatsapp_number" type="tel"
                                class="form-input @error('whatsapp_number') has-error @enderror"
                                value="{{ old('whatsapp_number') }}" placeholder="514-123-4567" autocomplete="tel"
                                inputmode="numeric" pattern="[0-9\-\s]{10,20}" minlength="10" maxlength="15"
                                aria-describedby="whatsapp-error whatsapp-hint" @disabled(!$isProvider)>
                            <div class="phone-hint" id="whatsapp-hint">
                                <i class="fas fa-lightbulb"></i>
                                <span>{{ __('auth.phone_auto_format') ?? 'Type your Canadian WhatsApp number without the country code' }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="field-error" id="whatsapp-error">
                        @error('whatsapp_number')<i class="fas fa-exclamation-circle"></i>{{ $message }}@enderror
                    </div>
                </div>
            </div>

            <label class="terms">
                <input type="checkbox" name="terms" value="1" @checked(old('terms')) required>
                <span>{{ __('auth.agree_terms_privacy') }}</span>
            </label>
            @error('terms')
                <div class="field-error" style="margin-top:-12px;margin-bottom:14px;">
                    <i class="fas fa-exclamation-circle"></i>{{ $message }}
                </div>
            @enderror

            <button type="submit" class="submit" id="google-complete-submit"
                data-label-client="{{ __('auth.google_finish_client') }}"
                data-label-provider="{{ __('auth.google_finish_provider') }}">
                {{ $selectedRole === 'service_provider' ? __('auth.google_finish_provider') : __('auth.google_finish_client') }}
            </button>
        </form>

        <a href="{{ route('register') }}" class="bail">{{ __('general.cancel') }}</a>
    </div>

    <script>
        (function () {
            const form = document.getElementById('google-complete-form');
            const fields = document.getElementById('provider-fields');
            const submit = document.getElementById('google-complete-submit');
            const roles = document.querySelectorAll('input[name="role"]');
            const mobile = document.getElementById('mobile');
            const whatsapp = document.getElementById('whatsapp_number');

            function initSelect(prefix) {
                const trigger = document.getElementById(prefix + '-trigger');
                const options = document.getElementById(prefix + '-options');
                const input = document.getElementById(prefix + '-input');
                const value = document.getElementById(prefix + '-value');

                function close() {
                    options.classList.remove('show');
                    trigger.classList.remove('active');
                    trigger.setAttribute('aria-expanded', 'false');
                }

                function choose(option) {
                    input.value = option.dataset.value || '';
                    value.textContent = option.textContent.trim();
                    value.classList.toggle('placeholder', !input.value);
                    options.querySelectorAll('.select-option').forEach(item => item.classList.remove('selected'));
                    option.classList.add('selected');
                    trigger.classList.remove('has-error');
                    document.getElementById(prefix + '-error').innerHTML = '';
                    close();
                }

                trigger.addEventListener('click', function () {
                    const opening = !options.classList.contains('show');
                    document.querySelectorAll('.select-options.show').forEach(open => open.classList.remove('show'));
                    document.querySelectorAll('.select-trigger.active').forEach(open => open.classList.remove('active'));
                    options.classList.toggle('show', opening);
                    trigger.classList.toggle('active', opening);
                    trigger.setAttribute('aria-expanded', opening ? 'true' : 'false');
                });

                options.querySelectorAll('.select-option').forEach(option => {
                    option.addEventListener('click', () => choose(option));
                });

                if (input.value) {
                    const selected = Array.from(options.querySelectorAll('.select-option'))
                        .find(option => option.dataset.value === input.value);
                    if (selected) choose(selected);
                }

                return { input, trigger, close };
            }

            const profession = initSelect('profession');
            const city = initSelect('city');

            document.addEventListener('click', function (event) {
                if (!document.getElementById('profession-select').contains(event.target)) profession.close();
                if (!document.getElementById('city-select').contains(event.target)) city.close();
            });

            function formatPhone(input) {
                let digits = input.value.replace(/\D/g, '');

                if (digits.length === 11 && digits.startsWith('1')) {
                    digits = digits.substring(1);
                }

                digits = digits.substring(0, 10);

                if (digits.length > 6) {
                    input.value = digits.substring(0, 3) + '-' + digits.substring(3, 6) + '-' + digits.substring(6);
                } else if (digits.length > 3) {
                    input.value = digits.substring(0, 3) + '-' + digits.substring(3);
                } else {
                    input.value = digits;
                }
            }

            function validateCanadianPhone(input, error, required) {
                const digits = input.value.replace(/\D/g, '');
                const validLength = digits.length === 10;
                const validFirstDigit = validLength && !['0', '1'].includes(digits[0]);
                const validFourthDigit = !required || (validLength && !['0', '1'].includes(digits[3]));
                const valid = (!required && digits.length === 0)
                    || (validLength && validFirstDigit && validFourthDigit);

                input.classList.toggle('has-error', !valid);

                if (!valid) {
                    const message = digits.length === 0
                        ? @json(__('validation.mobile_required_provider'))
                        : (!validLength
                            ? 'Please enter 10 digits'
                            : (!validFirstDigit ? 'First digit must be 2-9' : 'Fourth digit must be 2-9'));
                    error.innerHTML = '<i class="fas fa-exclamation-circle"></i>' + message;
                } else {
                    error.innerHTML = '';
                }

                return valid;
            }

            [mobile, whatsapp].forEach(input => {
                input.addEventListener('input', function () {
                    formatPhone(input);
                    const valid = validateCanadianPhone(
                        input,
                        document.getElementById(input.id === 'mobile' ? 'mobile-error' : 'whatsapp-error'),
                        input.id === 'mobile'
                    );

                    if (input.id === 'mobile') {
                        document.getElementById('phone-format').hidden = !valid || input.value === '';
                    }
                });
            });

            function apply() {
                const isProvider = document.querySelector('input[name="role"]:checked')?.value === 'service_provider';

                fields.hidden = !isProvider;
                fields.querySelectorAll('[name]').forEach(input => input.disabled = !isProvider);
                mobile.required = isProvider;

                submit.textContent = isProvider
                    ? submit.dataset.labelProvider
                    : submit.dataset.labelClient;
            }

            roles.forEach(function (input) {
                input.addEventListener('change', apply);
            });

            form.addEventListener('submit', function (event) {
                const isProvider = document.querySelector('input[name="role"]:checked')?.value === 'service_provider';

                if (!isProvider) return;

                let valid = true;

                if (!profession.input.value) {
                    document.getElementById('profession-error').innerHTML = '<i class="fas fa-exclamation-circle"></i>' + @json(__('validation.profession_required'));
                    profession.trigger.classList.add('has-error');
                    valid = false;
                }

                if (!city.input.value) {
                    document.getElementById('city-error').innerHTML = '<i class="fas fa-exclamation-circle"></i>' + @json(__('validation.city_required'));
                    city.trigger.classList.add('has-error');
                    valid = false;
                }

                valid = validateCanadianPhone(mobile, document.getElementById('mobile-error'), true) && valid;
                valid = validateCanadianPhone(whatsapp, document.getElementById('whatsapp-error'), false) && valid;

                if (!valid) event.preventDefault();
            });

            apply();
        })();
    </script>
</body>

</html>
