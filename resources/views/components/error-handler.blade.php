{{--
Unified Error Handler Component
Usage: <x-error-handler />

Features:
- Session flash messages (success, error, warning, info)
- Validation errors summary
- Inline field errors
- Toast notifications
- Multi-language support
--}}

{{-- Session Flash Messages --}}
@if(session('success') || session('error') || session('warning') || session('info'))
    <div class="alert-container mb-4" role="alert">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fas fa-check-circle me-2 fs-5"></i>
                    <div class="flex-grow-1">
                        <strong>{{ __('general.success') }}!</strong>
                        <div>{{ session('success') }}</div>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fas fa-exclamation-circle me-2 fs-5"></i>
                    <div class="flex-grow-1">
                        <strong>{{ __('general.error') }}!</strong>
                        <div>{{ session('error') }}</div>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('warning'))
            <div class="alert alert-warning alert-dismissible fade show shadow-sm" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fas fa-exclamation-triangle me-2 fs-5"></i>
                    <div class="flex-grow-1">
                        <strong>{{ __('general.warning') }}!</strong>
                        <div>{{ session('warning') }}</div>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('info'))
            <div class="alert alert-info alert-dismissible fade show shadow-sm" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fas fa-info-circle me-2 fs-5"></i>
                    <div class="flex-grow-1">
                        <strong>{{ __('general.info') }}!</strong>
                        <div>{{ session('info') }}</div>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
    </div>
@endif

{{-- Validation Errors Summary --}}
@if($errors->any())
    <div class="validation-errors-container mb-4" role="alert">
        <div class="validation-error-box">
            <div class="error-icon-wrapper">
                <i class="fas fa-exclamation-circle"></i>
            </div>
            <div class="error-content">
                <h6 class="error-title">
                    {{ __('validation.error_title') }}
                </h6>
                <p class="error-description">{{ __('validation.please_correct_errors') }}</p>
                <ul class="error-list">
                    @foreach($errors->all() as $error)
                        <li class="error-item">
                            <i class="fas fa-times-circle"></i>
                            <span>{{ $error }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
            <button type="button" class="error-close-btn" onclick="this.parentElement.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>
@endif

{{-- Auto-dismiss alerts after 5 seconds --}}
@if(session('success') || session('error') || session('warning') || session('info'))
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            setTimeout(function () {
                const alerts = document.querySelectorAll('.alert-container .alert');
                alerts.forEach(function (alert) {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                });
            }, 5000);
        });
    </script>
@endif

<style>
    /* Alert Container Styles */
    .alert-container .alert {
        border-radius: 12px;
        border-left-width: 4px;
        animation: slideInDown 0.4s ease-out;
    }

    /* Validation Errors Box - Beautiful & Clear */
    .validation-errors-container {
        animation: slideInDown 0.4s ease-out;
    }

    .validation-error-box {
        position: relative;
        background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
        border: 2px solid #ef4444;
        border-left-width: 6px;
        border-radius: 16px;
        padding: 1.5rem;
        box-shadow: 0 8px 24px rgba(239, 68, 68, 0.2);
        display: flex;
        gap: 1rem;
        align-items: start;
    }

    .error-icon-wrapper {
        flex-shrink: 0;
        width: 48px;
        height: 48px;
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
    }

    .error-icon-wrapper i {
        color: white;
        font-size: 24px;
    }

    .error-content {
        flex: 1;
    }

    .error-title {
        font-size: 1.125rem;
        font-weight: 700;
        color: #991b1b;
        margin: 0 0 0.5rem 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .error-description {
        font-size: 0.9375rem;
        color: #b91c1c;
        margin: 0 0 1rem 0;
        font-weight: 500;
    }

    .error-list {
        list-style: none;
        padding: 0;
        margin: 0;
        display: flex;
        flex-direction: column;
        gap: 0.625rem;
    }

    .error-item {
        display: flex;
        align-items: start;
        gap: 0.625rem;
        padding: 0.75rem 1rem;
        background: white;
        border-radius: 10px;
        font-size: 0.9375rem;
        color: #991b1b;
        font-weight: 500;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .error-item:hover {
        transform: translateX(4px);
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.15);
    }

    .error-item i {
        color: #ef4444;
        font-size: 1rem;
        margin-top: 2px;
        flex-shrink: 0;
    }

    .error-item span {
        flex: 1;
        line-height: 1.5;
    }

    .error-close-btn {
        flex-shrink: 0;
        width: 32px;
        height: 32px;
        background: white;
        border: 2px solid #dc2626;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s ease;
        color: #dc2626;
        font-size: 14px;
    }

    .error-close-btn:hover {
        background: #dc2626;
        color: white;
        transform: rotate(90deg);
    }

    /* Animation */
    @keyframes slideInDown {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Success/Warning/Info Alert Styles */
    .alert-success {
        border-left-color: #10b981;
        background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
    }

    .alert-danger {
        border-left-color: #ef4444;
        background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
    }

    .alert-warning {
        border-left-color: #f59e0b;
        background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
    }

    .alert-info {
        border-left-color: #3b82f6;
        background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
    }

    /* RTL Support */
    [dir="rtl"] .validation-error-box {
        border-left-width: 2px;
        border-right-width: 6px;
    }

    [dir="rtl"] .error-item:hover {
        transform: translateX(-4px);
    }
</style>