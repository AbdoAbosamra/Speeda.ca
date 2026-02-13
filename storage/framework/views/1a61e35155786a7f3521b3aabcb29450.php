

<?php $__env->startSection('content'); ?>
<div class="container mt-5">
    <h1>تشخيص مشكلة الصور</h1>
    
    <h2>🔍 فحص قاعدة البيانات:</h2>
    <ul>
        <li><strong>عدد مزودي الخدمة الكلي:</strong> <?php echo e($totalSP); ?></li>
        <li><strong>مزودي خدمة بـ profile_image:</strong> <?php echo e($spWithImages); ?></li>
        <li><strong>الملفات الموجودة في storage:</strong> <?php echo e($storageCount); ?></li>
    </ul>

    <?php if($totalSP === 0): ?>
        <div class="alert alert-danger">
            ❌ <strong>لا توجد مزودي خدمة في الـ database!</strong>
            <p>الحل: اذهب لصفحة التسجيل وأنشئ حساب مزود خدمة أولاً.</p>
        </div>
    <?php elseif($spWithImages === 0): ?>
        <div class="alert alert-warning">
            ⚠️ <strong>لا توجد صور مرفوعة!</strong>
            <p>الحل: سجل دخول كـ مزود خدمة ورفع صورة ملفك الشخصي.</p>
        </div>
    <?php endif; ?>

    <h2>📋 أول 3 مزودي خدمة:</h2>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>النسبة</th>
                <th>profile_image​ (DB)</th>
                <th>profile_image_url (Accessor)</th>
                <th>الحالة</th>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $serviceProviders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td><?php echo e($sp->id); ?></td>
                    <td><?php echo e($sp->company_name); ?></td>
                    <td>
                        <?php if($sp->profile_image): ?>
                            <code style="word-break: break-all;"><?php echo e($sp->profile_image); ?></code>
                        <?php else: ?>
                            <span class="text-muted">NULL</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <code style="word-break: break-all;"><?php echo e($sp->profile_image_url); ?></code>
                    </td>
                    <td>
                        <?php if($sp->profile_image && file_exists(storage_path('app/public/' . $sp->profile_image))): ?>
                            ✅ موجود
                        <?php elseif($sp->profile_image): ?>
                            ❌ في DB لكن ليس في الـ storage!
                        <?php else: ?>
                            ⚪ placeholder
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="5" class="text-center text-muted">لا توجد مزودي خدمة</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <h2>📁 الملفات الموجودة في storage:</h2>
    <ul>
        <?php $__currentLoopData = $storageFiles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $file): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <li><code><?php echo e($file); ?></code></li>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </ul>

    <p class="mt-4 text-muted">
        <small>
            ملاحظة: هذه الصفحة للتشخيص فقط وسيتم حذفها.
        </small>
    </p>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH Y:\Speeda - Versions\Speeda\resources\views/diagnostic.blade.php ENDPATH**/ ?>