


<?php if(auth()->check() && isset($serviceProvider) && auth()->id() === $serviceProvider->user_id): ?>
    <?php
        $showPopup = is_null($serviceProvider->profile_completion_popup_shown_at)
                     && ($serviceProvider->profile_completion_percent ?? 0) < 100;
        $showBanner = !is_null($serviceProvider->profile_completion_popup_shown_at)
                      && ($serviceProvider->profile_completion_percent ?? 0) < 100;
    ?>

    
    <?php if($showPopup): ?>
        <div class="modal fade" id="profileCompletionPopup" tabindex="-1" aria-labelledby="profileCompletionPopupLabel" aria-hidden="true" style="z-index: 99999 !important;">
            <div class="modal-dialog modal-dialog-centered" style="z-index: 99999 !important;">
                <div class="modal-content" style="border-radius: 20px; border: none; overflow: hidden;">
                    <div class="modal-header border-0 text-white" style="background: linear-gradient(135deg, #4361ee 0%, #f72585 100%); padding: 1.5rem;">
                        <h5 class="modal-title fw-bold" id="profileCompletionPopupLabel">
                            <i class="fas fa-rocket me-2"></i><?php echo e(__('service_provider.popup_title')); ?>

                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        
                        <div class="text-center mb-4">
                            <div class="position-relative d-inline-block" style="width: 100px; height: 100px;">
                                <svg viewBox="0 0 36 36" class="circular-chart" style="width: 100%; height: 100%;">
                                    <path class="circle-bg" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                                          fill="none" stroke="#e9ecef" stroke-width="3"/>
                                    <path class="circle" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                                          fill="none" stroke="#4361ee" stroke-width="3"
                                          stroke-dasharray="<?php echo e($serviceProvider->profile_completion_percent ?? 0); ?>, 100"
                                          stroke-linecap="round"/>
                                </svg>
                                <div class="position-absolute top-50 start-50 translate-middle fw-bold" style="font-size: 1.2rem; color: #4361ee;">
                                    <?php echo e($serviceProvider->profile_completion_percent ?? 0); ?>%
                                </div>
                            </div>
                        </div>

                        <p class="text-center mb-3" style="font-size: 1.05rem; line-height: 1.8; color: #333;">
                            <?php echo e(__('service_provider.popup_body')); ?>

                        </p>

                        <div class="d-flex flex-column gap-2 mt-3">
                            <div class="d-flex align-items-center p-2 rounded" style="background: rgba(67,97,238,0.08);">
                                <i class="fas fa-search-plus text-primary me-2"></i>
                                <small class="fw-semibold"><?php echo e(__('service_provider.popup_benefit_visibility')); ?></small>
                            </div>
                            <div class="d-flex align-items-center p-2 rounded" style="background: rgba(247,37,133,0.08);">
                                <i class="fas fa-users text-danger me-2"></i>
                                <small class="fw-semibold"><?php echo e(__('service_provider.popup_benefit_clients')); ?></small>
                            </div>
                            <div class="d-flex align-items-center p-2 rounded" style="background: rgba(76,175,80,0.08);">
                                <i class="fas fa-chart-line text-success me-2"></i>
                                <small class="fw-semibold"><?php echo e(__('service_provider.popup_benefit_opportunities')); ?></small>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 justify-content-center p-4">
                        <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal" id="popupDismissBtn">
                            <?php echo e(__('service_provider.popup_dismiss')); ?>

                        </button>
                        <a href="#profileUpdateForm" class="btn text-white rounded-pill px-4" data-bs-dismiss="modal"
                           style="background: linear-gradient(135deg, #4361ee, #f72585); border: none;">
                            <i class="fas fa-edit me-2"></i><?php echo e(__('service_provider.popup_complete_profile')); ?>

                        </a>
                    </div>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Show the popup
                var popup = new bootstrap.Modal(document.getElementById('profileCompletionPopup'));
                popup.show();

                // Mark as shown when dismissed (via close button, backdrop click, or dismiss button)
                document.getElementById('profileCompletionPopup').addEventListener('hidden.bs.modal', function () {
                    fetch('<?php echo e(route("service-providers.popup-dismissed")); ?>', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        }
                    }).catch(function() {}); // Silently ignore errors
                });
            });
        </script>
    <?php endif; ?>

    
    <?php if($showBanner): ?>
        <div class="alert border-0 shadow-sm mb-4" role="alert"
             style="background: linear-gradient(135deg, rgba(67,97,238,0.08), rgba(247,37,133,0.08)); border-radius: 16px; padding: 1.25rem;">
            <div class="d-flex align-items-center gap-3 flex-wrap">
                <div class="d-flex align-items-center gap-2 flex-grow-1">
                    <i class="fas fa-info-circle text-primary fa-lg"></i>
                    <div>
                        <strong><?php echo e(__('service_provider.banner_complete_profile')); ?></strong>
                        <span class="text-muted ms-1">
                            (<?php echo e($serviceProvider->profile_completion_percent ?? 0); ?>% <?php echo e(__('service_provider.banner_completed')); ?>)
                        </span>
                    </div>
                </div>
                <a href="#profileUpdateForm" class="btn btn-sm btn-primary rounded-pill px-3">
                    <i class="fas fa-arrow-right me-1"></i><?php echo e(__('service_provider.popup_complete_profile')); ?>

                </a>
            </div>
        </div>
    <?php endif; ?>
<?php endif; ?>
<?php /**PATH Y:\Speeda - Versions\Speeda\resources\views/components/profile-completion-popup.blade.php ENDPATH**/ ?>