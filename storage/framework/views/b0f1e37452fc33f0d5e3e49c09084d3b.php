

<!-- Toast Container -->
<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 9999;">
    <div id="globalToast" class="toast align-items-center border-0 shadow-lg" role="alert" aria-live="assertive"
        aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body d-flex align-items-center">
                <i class="toast-icon fas me-2 fs-5"></i>
                <div class="flex-grow-1">
                    <strong class="toast-title d-block"></strong>
                    <span class="toast-message"></span>
                </div>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
                aria-label="Close"></button>
        </div>
    </div>
</div>

<style>
    .toast {
        min-width: 300px;
        border-radius: 12px;
        backdrop-filter: blur(10px);
    }

    .toast.toast-success {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
        border-left: 4px solid #047857;
    }

    .toast.toast-error {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: white;
        border-left: 4px solid #b91c1c;
    }

    .toast.toast-warning {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: white;
        border-left: 4px solid #b45309;
    }

    .toast.toast-info {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        color: white;
        border-left: 4px solid #1d4ed8;
    }

    .toast-body {
        padding: 1rem;
    }

    .toast-title {
        font-weight: 600;
        margin-bottom: 0.25rem;
    }

    .toast-message {
        font-size: 0.9rem;
        opacity: 0.95;
    }
</style>

<script>
    /**
     * Global Toast Notification Function
     * @param {string} message - The message to display
     * @param {string} type - Type: 'success', 'error', 'warning', 'info' (default: 'info')
     * @param {string} title - Optional title (default: based on type)
     * @param {number} duration - Duration in ms (default: 5000)
     */
    window.showToast = function (message, type = 'info', title = '', duration = 5000) {
        const toastEl = document.getElementById('globalToast');
        const toast = new bootstrap.Toast(toastEl, {
            autohide: true,
            delay: duration
        });

        // Reset classes
        toastEl.className = 'toast align-items-center border-0 shadow-lg';
        toastEl.classList.add('toast-' + type);

        // Set icon
        const icon = toastEl.querySelector('.toast-icon');
        icon.className = 'toast-icon fas me-2 fs-5';

        switch (type) {
            case 'success':
                icon.classList.add('fa-check-circle');
                title = title || '<?php echo e(__("general.success")); ?>';
                break;
            case 'error':
                icon.classList.add('fa-exclamation-circle');
                title = title || '<?php echo e(__("general.error")); ?>';
                break;
            case 'warning':
                icon.classList.add('fa-exclamation-triangle');
                title = title || '<?php echo e(__("general.warning")); ?>';
                break;
            case 'info':
                icon.classList.add('fa-info-circle');
                title = title || '<?php echo e(__("general.info")); ?>';
                break;
        }

        // Set content
        toastEl.querySelector('.toast-title').textContent = title;
        toastEl.querySelector('.toast-message').innerHTML = message;

        // Show toast
        toast.show();
    };

    // Handle Laravel flash messages on page load
    document.addEventListener('DOMContentLoaded', function () {
        <?php if(session('toast_success')): ?>
            showToast('<?php echo e(session("toast_success")); ?>', 'success');
        <?php endif; ?>

        <?php if(session('toast_error')): ?>
            showToast('<?php echo e(session("toast_error")); ?>', 'error');
        <?php endif; ?>

        <?php if(session('toast_warning')): ?>
            showToast('<?php echo e(session("toast_warning")); ?>', 'warning');
        <?php endif; ?>

        <?php if(session('toast_info')): ?>
            showToast('<?php echo e(session("toast_info")); ?>', 'info');
        <?php endif; ?>
    });

    // Global AJAX error handler
    if (typeof $ !== 'undefined') {
        $(document).ajaxError(function (event, jqXHR, ajaxSettings, thrownError) {
            if (jqXHR.status === 419) {
                showToast('<?php echo e(__("validation.session_expired")); ?>', 'error', '<?php echo e(__("general.error")); ?>');
            } else if (jqXHR.status === 401) {
                showToast('<?php echo e(__("validation.unauthorized")); ?>', 'error', '<?php echo e(__("general.error")); ?>');
            } else if (jqXHR.status === 403) {
                showToast('<?php echo e(__("validation.forbidden")); ?>', 'error', '<?php echo e(__("general.error")); ?>');
            } else if (jqXHR.status === 404) {
                showToast('<?php echo e(__("validation.not_found")); ?>', 'error', '<?php echo e(__("general.error")); ?>');
            } else if (jqXHR.status === 422) {
                // Validation errors
                if (jqXHR.responseJSON && jqXHR.responseJSON.errors) {
                    const errors = Object.values(jqXHR.responseJSON.errors).flat();
                    showToast(errors.join('<br>'), 'error', '<?php echo e(__("validation.error_title")); ?>');
                }
            } else if (jqXHR.status >= 500) {
                showToast('<?php echo e(__("validation.server_error")); ?>', 'error', '<?php echo e(__("general.error")); ?>');
            }
        });
    }
</script><?php /**PATH Y:\Speeda - Versions\Speeda\resources\views/components/toast-notification.blade.php ENDPATH**/ ?>