# TODO: إصلاح معرض الصور في Service Provider Gallery - خطة نهائية

## ✅ **مكتمل [0/8]**

### 1. [ ] إنشاء ملف تشخيص شامل للـ MediaLibrary
```
resources/views/service-providers/gallery-diagnostic.blade.php
```

### 2. [ ] فحص وجود ServiceProviders و Media table
```
php artisan tinker → ServiceProvider::count() + media count
```

### 3. [ ] إضافة صور تجريبية لأول ServiceProvider
```
Tinker: $sp->addMedia(...) → 'provider_gallery'
```

### 4. [ ] إصلاح/تشغيل Queue للـ Conversions
```
php artisan queue:work --tries=3
php artisan media-library:regenerate
```

### 5. [ ] تحسين Controller upload handler
```
ServiceProviderController.php → handle gallery_images[]
```

### 6. [ ] إضافة fallback placeholders في View
```
show.blade.php → لو فارغ يعرض 'Upload your gallery'
```

### 7. [ ] إضافة route للتشخيص
```
web.php → Route::get('/service-providers/{id}/gallery-diagnostic'
```

### 8. [ ] اختبار كامل + cleanup
```
اختبار على 3 providers → حذف diagnostic files
```

**Next Step: 1/8**
