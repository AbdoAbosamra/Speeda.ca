

<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['serviceProvider']));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['serviceProvider']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $isLoggedIn = auth()->check();
    $isClient = $isLoggedIn && auth()->user()->isClient();
    $isEndorsed = $isLoggedIn ? $serviceProvider->isEndorsedBy(auth()->id()) : false;
    $endorsementCount = $serviceProvider->endorsement_count ?? 0;
?>

<div x-data="{
        endorsed: <?php echo e($isEndorsed ? 'true' : 'false'); ?>,
        count: <?php echo e($endorsementCount); ?>,
        loading: false,
        isLoggedIn: <?php echo e($isLoggedIn ? 'true' : 'false'); ?>,
        isClient: <?php echo e($isClient ? 'true' : 'false'); ?>,

        async toggle() {
            if (!this.isLoggedIn) {
                // Redirect to login
                window.location.href = '<?php echo e(route('login')); ?>?redirect=' + encodeURIComponent(window.location.href);
                return;
            }

            if (!this.isClient) {
                // Show toast message
                this.$dispatch('toast', { message: '<?php echo e(__('endorsements.clients_only')); ?>', type: 'warning' });
                return;
            }

            if (this.loading) return;

            this.loading = true;

            try {
                const response = await fetch('<?php echo e(route('endorsements.toggle', $serviceProvider->id)); ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content'),
                        'Accept': 'application/json',
                    },
                });

                const data = await response.json();

                if (data.success) {
                    this.endorsed = data.endorsed;
                    this.count = data.count;
                    this.$dispatch('toast', { message: data.message, type: 'success' });
                } else {
                    this.$dispatch('toast', { message: data.message, type: 'error' });
                }
            } catch (error) {
                console.error('Endorsement error:', error);
                this.$dispatch('toast', { message: '<?php echo e(__('general.error_occurred')); ?>', type: 'error' });
            } finally {
                this.loading = false;
            }
        }
    }" class="endorsement-wrapper">
    <button @click="toggle()" :disabled="loading" :class="{
            'endorsed': endorsed,
            'loading': loading
        }" class="endorsement-btn"
        :title="endorsed ? '<?php echo e(__('endorsements.recommended')); ?>' : '<?php echo e(__('endorsements.recommend')); ?>'"
        aria-label="<?php echo e(__('endorsements.recommend')); ?>">
        
        <span x-show="loading" class="endorsement-spinner">
            <svg class="animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" width="20"
                height="20">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                </path>
            </svg>
        </span>

        
        <span x-show="!loading" class="endorsement-icon">
            
            <template x-if="!endorsed">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor" width="20" height="20">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M6.633 10.25c.806 0 1.533-.446 2.031-1.08a9.041 9.041 0 0 1 2.861-2.4c.723-.384 1.35-.956 1.653-1.715a4.498 4.498 0 0 0 .322-1.672V2.75a.75.75 0 0 1 .75-.75 2.25 2.25 0 0 1 2.25 2.25c0 1.152-.26 2.243-.723 3.218-.266.558.107 1.282.725 1.282m0 0h3.126c1.026 0 1.945.694 2.054 1.715.045.422.068.85.068 1.285a11.95 11.95 0 0 1-2.649 7.521c-.388.482-.987.729-1.605.729H13.48c-.483 0-.964-.078-1.423-.23l-3.114-1.04a4.501 4.501 0 0 0-1.423-.23H5.904m7.72-9.75V10m0 0v9.75M5.904 10H3.75a.75.75 0 0 1-.75-.75v-3.75a.75.75 0 0 1 .75-.75h1.673a2.25 2.25 0 0 1 1.573.64l.908.909" />
                </svg>
            </template>
            <template x-if="endorsed">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="20" height="20">
                    <path
                        d="M7.493 18.5c-.425 0-.82-.236-.975-.632A7.48 7.48 0 0 1 6 15.125c0-1.75.599-3.358 1.602-4.634.151-.192.373-.309.6-.397.473-.183.89-.514 1.212-.924a9.042 9.042 0 0 1 2.861-2.4c.723-.384 1.35-.956 1.653-1.715a4.498 4.498 0 0 0 .322-1.672V2.75A.75.75 0 0 1 15 2a2.25 2.25 0 0 1 2.25 2.25c0 1.152-.26 2.243-.723 3.218-.266.558.107 1.282.725 1.282h3.126c1.026 0 1.945.694 2.054 1.715.045.422.068.85.068 1.285a11.95 11.95 0 0 1-2.649 7.521c-.388.482-.987.729-1.605.729H14.23c-.483 0-.964-.078-1.423-.23l-3.114-1.04a4.501 4.501 0 0 0-1.423-.23h-.777ZM2.331 10.727a11.969 11.969 0 0 0-.831 4.398 12 12 0 0 0 .52 3.507C2.28 19.482 3.105 20 3.994 20H4.9c.445 0 .72-.498.523-.898a8.963 8.963 0 0 1-.924-3.977c0-1.708.476-3.305 1.302-4.666.245-.403-.028-.959-.5-.959H4.25c-.832 0-1.612.453-1.918 1.227Z" />
                </svg>
            </template>
        </span>

        
        <span class="endorsement-label"
            x-text="endorsed ? '<?php echo e(__('endorsements.recommended')); ?>' : '<?php echo e(__('endorsements.recommend')); ?>'"></span>

        
        <span x-show="count > 0" class="endorsement-count" x-text="count"></span>
    </button>
</div>

<style>
    .endorsement-wrapper {
        display: inline-block;
    }

    .endorsement-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 18px;
        border-radius: 50px;
        font-weight: 600;
        font-size: 0.9rem;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        border: 2px solid rgba(67, 97, 238, 0.3);
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.9), rgba(240, 242, 255, 0.9));
        color: #4361ee;
        backdrop-filter: blur(10px);
        box-shadow: 0 4px 15px rgba(67, 97, 238, 0.15);
    }

    .endorsement-btn:hover:not(:disabled) {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(67, 97, 238, 0.25);
        border-color: rgba(67, 97, 238, 0.5);
    }

    .endorsement-btn:active:not(:disabled) {
        transform: translateY(0);
    }

    .endorsement-btn.endorsed {
        background: linear-gradient(135deg, #4361ee, #3f37c9);
        color: white;
        border-color: transparent;
        box-shadow: 0 4px 15px rgba(67, 97, 238, 0.4);
    }

    .endorsement-btn.endorsed:hover:not(:disabled) {
        background: linear-gradient(135deg, #3a58e0, #3830b8);
        box-shadow: 0 6px 20px rgba(67, 97, 238, 0.5);
    }

    .endorsement-btn:disabled {
        opacity: 0.7;
        cursor: not-allowed;
    }

    .endorsement-icon,
    .endorsement-spinner {
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .endorsement-count {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 24px;
        height: 24px;
        padding: 0 8px;
        border-radius: 12px;
        font-size: 0.8rem;
        font-weight: 700;
        background: rgba(255, 255, 255, 0.3);
        margin-left: 4px;
    }

    .endorsement-btn.endorsed .endorsement-count {
        background: rgba(255, 255, 255, 0.25);
    }

    .animate-spin {
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        from {
            transform: rotate(0deg);
        }

        to {
            transform: rotate(360deg);
        }
    }

    /* RTL Support */
    [dir="rtl"] .endorsement-btn {
        flex-direction: row-reverse;
    }

    [dir="rtl"] .endorsement-count {
        margin-left: 0;
        margin-right: 4px;
    }

    /* Responsive */
    @media (max-width: 576px) {
        .endorsement-btn {
            padding: 8px 14px;
            font-size: 0.85rem;
        }

        .endorsement-label {
            display: none;
        }
    }
</style>
<?php /**PATH Y:\Speeda - Versions\Speeda\resources\views/components/endorsement-button.blade.php ENDPATH**/ ?>