
<?php if(session('notification') || $errors->any()): ?>
    <div class="notification-card-container"
         x-data="{ show: true }"
         x-show="show"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform translate-y-2"
         x-transition:enter-end="opacity-100 transform translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         x-init="setTimeout(() => show = false, 5000)"
         style="position: fixed; top: 20px; right: 20px; z-index: 9999; max-width: 400px;">

        <?php
            $notification = session('notification');
            $type = $notification['type'] ?? 'error';
            $message = $notification['message'] ?? ($errors->any() ? $errors->first() : '');
            $icon = $notification['icon'] ?? 'fas fa-exclamation-circle';

            $bgColor = match($type) {
                'success' => 'bg-green-50 border-green-500',
                'error' => 'bg-red-50 border-red-500',
                'warning' => 'bg-yellow-50 border-yellow-500',
                'info' => 'bg-blue-50 border-blue-500',
                default => 'bg-gray-50 border-gray-500',
            };

            $textColor = match($type) {
                'success' => 'text-green-800',
                'error' => 'text-red-800',
                'warning' => 'text-yellow-800',
                'info' => 'text-blue-800',
                default => 'text-gray-800',
            };

            $iconColor = match($type) {
                'success' => 'text-green-600',
                'error' => 'text-red-600',
                'warning' => 'text-yellow-600',
                'info' => 'text-blue-600',
                default => 'text-gray-600',
            };
        ?>

        <div class="notification-card <?php echo e($bgColor); ?> border-l-4 rounded-lg shadow-lg p-4">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <i class="<?php echo e($icon); ?> <?php echo e($iconColor); ?> text-xl"></i>
                </div>
                <div class="ml-3 flex-1">
                    <p class="<?php echo e($textColor); ?> text-sm font-medium">
                        <?php echo e($message); ?>

                    </p>

                    <?php if($errors->count() > 1): ?>
                        <ul class="mt-2 <?php echo e($textColor); ?> text-xs list-disc list-inside">
                            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li><?php echo e($error); ?></li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                    <?php endif; ?>
                </div>
                <div class="ml-3 flex-shrink-0">
                    <button @click="show = false" class="<?php echo e($textColor); ?> hover:opacity-70">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>


<?php if (! $__env->hasRenderedOnce('d87e5878-ee9c-45a6-bb3d-bd3bcede4f41')): $__env->markAsRenderedOnce('d87e5878-ee9c-45a6-bb3d-bd3bcede4f41'); ?>
    <?php $__env->startPush('scripts'); ?>
        <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <?php $__env->stopPush(); ?>
<?php endif; ?>
<?php /**PATH Y:\Speeda - Versions\Speeda\resources\views/components/notification-card.blade.php ENDPATH**/ ?>