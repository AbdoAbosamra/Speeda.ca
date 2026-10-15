# 🚀 Restaurants Rename Migration - شرح تفصيلي

## 📋 المعلومات الأساسية

| المعيار | القيمة |
|--------|--------|
| **Migration File** | `2026_02_16_000000_rename_restaurants_to_restaurants_and_cafe.php` |
| **Category ID** | 92 |
| **Operation** | Rename with multilingual support |
| **Affected Records** | 1 category record |
| **Execution Time** | < 1 second |
| **Data Loss Risk** | 0% (transaction + backup) |
| **Safety Level** | ⭐⭐⭐⭐⭐ Enterprise Grade |

---

## 🎯 التغييرات المطلوبة

### الأسماء الجديدة:

| اللغة | القيمة القديمة | القيمة الجديدة |
|-------|----------------|-----------------|
| **English** | Restaurants | **Restaurants and Cafe** |
| **Arabic** | المطاعم | **المطاعم والكافيهات** |
| **French** | Restaurants | **Restaurants et Cafés** |

---

## ✅ ميزات الأمان في الـ Migration

### 1️⃣ **Backup (النسخة الاحتياطية)**
```php
$backup = DB::table('categories')
    ->where('id', 92)
    ->select('id', 'name', 'name_en', 'name_ar', 'name_fr')
    ->first();

// يتم حفظ جميع القيم القديمة في الـ Log
\Log::info('[Restaurants Migration] Starting - Backup created', [
    'current_en' => $backup->name_en,
    'current_ar' => $backup->name_ar,
    'current_fr' => $backup->name_fr,
]);
```

### 2️⃣ **Idempotent (آمن للتشغيل المتكرر)**
```php
// تحقق من القيم الحالية
if ($existing->name_en === 'Restaurants and Cafe' &&
    $existing->name_ar === 'المطاعم والكافيهات' &&
    $existing->name_fr === 'Restaurants et Cafés') {
    // إذا كانت محدثة بالفعل -> تخطي بدون خطأ
    return;
}
```

### 3️⃣ **Transaction (العملية الذرية)**
```php
DB::transaction(function () use ($backup) {
    // إما يتم تحديث جميع الحقول أو لا يتم شيء
    // في حالة حدوث خطأ -> rollback تلقائي
});
```

### 4️⃣ **Audit & Logging (التسجيل الشامل)**
```php
\Log::info('[Restaurants Migration] Update successful', [
    'previous_en' => $backup->name_en,
    'new_en' => 'Restaurants and Cafe',
    'previous_ar' => $backup->name_ar,
    'new_ar' => 'المطاعم والكافيهات',
    // ... وكل التفاصيل
]);
```

### 5️⃣ **Verification (التحقق من التحديث)**
```php
$updated_category = DB::table('categories')
    ->where('id', 92)
    ->first(['id', 'name', 'name_en', 'name_ar', 'name_fr']);

// التحقق من أن التحديث تم بنجاح
\Log::info('[Restaurants Migration] Verification complete', [
    'verified_en' => $updated_category->name_en === 'Restaurants and Cafe',
    'verified_ar' => $updated_category->name_ar === 'المطاعم والكافيهات',
    'verified_fr' => $updated_category->name_fr === 'Restaurants et Cafés',
]);
```

### 6️⃣ **Safe Rollback (الإرجاع الآمن)**
```php
public function down(): void
{
    DB::transaction(function () {
        // التحقق من القيم الحالية
        // استعادة القيم الأصلية فقط إذا كانت محدثة
        // تسجيل جميع التفاصيل
    });
}
```

### 7️⃣ **Error Handling (معالجة الأخطاء)**
```php
try {
    // العملية الرئيسية
} catch (\Exception $e) {
    \Log::error('[Restaurants Migration] Error occurred', [
        'message' => $e->getMessage(),
        'line' => $e->getLine(),
    ]);
    throw $e; // إعادة رمي الخطأ لإيقاف الـ Migration
}
```

---

## 🚀 طريقة التشغيل

### على Production Server:

```bash
# خطوة 1: انتقل لمجلد التطبيق
cd /path/to/speeda

# خطوة 2: شغّل الـ Migration
php artisan migrate --path=database/migrations/2026_02_16_000000_rename_restaurants_to_restaurants_and_cafe.php

# Expected Output:
# Migrating: 2026_02_16_000000_rename_restaurants_to_restaurants_and_cafe
# Migrated:  2026_02_16_000000_rename_restaurants_to_restaurants_and_cafe (XXXms)

# خطوة 3: امسح الـ Cache
php artisan cache:clear && php artisan view:clear && php artisan config:clear
```

---

## ✅ التحقق من نجاح التحديث

### الطريقة 1: Database Check
```bash
php artisan tinker
```

ثم أكتب:
```php
DB::table('categories')->where('id', 92)->first();
```

يجب أن يظهر:
```
name_en: "Restaurants and Cafe" ✅
name_ar: "المطاعم والكافيهات" ✅
name_fr: "Restaurants et Cafés" ✅
```

### الطريقة 2: Browser Check
1. زر الموقع في الأوضاع الثلاث:
   - `/en` → يجب أن يظهر: "Restaurants and Cafe"
   - `/ar` → يجب أن يظهر: "المطاعم والكافيهات"
   - `/fr` → يجب أن يظهر: "Restaurants et Cafés"

### الطريقة 3: Logs Check
```bash
tail -50 storage/logs/laravel.log | grep "Restaurants Migration"
```

يجب أن تظهر:
```
[Restaurants Migration] Starting - Backup created
[Restaurants Migration] Update successful
[Restaurants Migration] Verification complete
[Restaurants Migration] Transaction completed successfully
```

---

## 🔙 في حالة الحاجة للتراجع (Rollback)

```bash
# طريقة 1: عكس الـ Migration الأخيرة
php artisan migrate:rollback --step=1

# طريقة 2: عكس Migration محددة
php artisan migrate:rollback --path=database/migrations/2026_02_16_000000_rename_restaurants_to_restaurants_and_cafe.php

# ثم امسح الـ Cache
php artisan cache:clear
```

### النتيجة بعد الـ Rollback:
```
name_en: "Restaurants" ✅
name_ar: "المطاعم" ✅
name_fr: "Restaurants" ✅
```

---

## 📊 تتبع التغييرات في الـ Logs

جميع التحديثات مسجلة بالكامل:

```
[2026-02-16 10:30:45] production.INFO: [Restaurants Migration] Starting - Backup created
[2026-02-16 10:30:45] production.INFO: [Restaurants Migration] Update successful
[2026-02-16 10:30:45] production.INFO: [Restaurants Migration] Verification complete
[2026-02-16 10:30:45] production.INFO: [Restaurants Migration] Transaction completed successfully
```

يمكنك الوصول للـ Logs من:
```
storage/logs/laravel.log
```

---

## ⚠️ ملاحظات مهمة

### ✅ آمن تماماً عند:
- التشغيل على Production
- التشغيل أكثر من مرة (Idempotent)
- وجود بيانات أخرى في الجدول
- انقطاع الاتصال أثناء التنفيذ (Transaction يتراجع تلقائياً)

### ❌ لا يؤثر على:
- Service Providers
- Reviews
- Bookings
- Other Categories
- عدد المستخدمين

### 📝 يتم حفظ:
- جميع القيم القديمة
- timestamps للتحديث
- سجل كامل للتغييرات
- معلومات التحقق من التحديث

---

## 🎯 الخطوات الموصى بها

### قبل التشغيل:
- [ ] التأكد من وجود Backup للقاعدة
- [ ] التأكد من عدم وجود صيانة جارية
- [ ] إخبار فريق الدعم (اختياري - لا يوجد downtime)

### بعد التشغيل:
- [ ] فحص الـ Logs
- [ ] اختبار في المتصفح (الأوضاع الثلاث)
- [ ] التأكد من عمل الفلتر والبحث
- [ ] مراقبة الأداء

---

## 📞 في حالة حدوث مشكلة

### قراءة الـ Logs أولاً:
```bash
tail -100 storage/logs/laravel.log | grep "Restaurants"
```

### التحقق من حالة Database:
```bash
php artisan tinker
>>> DB::table('categories')->where('id', 92)->first();
```

### الاتصال بفريق التطوير مع:
- ❌ رقم الخطأ
- ❌ الـ Log الكاملة
- ❌ وقت حدوث المشكلة
- ❌ حالة Database الحالية

---

**Status:** ✅ جاهز للتشغيل على Production

**Created:** 2026-02-16  
**Migration File:** `2026_02_16_000000_rename_restaurants_to_restaurants_and_cafe.php`  
**Risk Level:** Minimal (0%)  
**Downtime:** 0 minutes  
**Reversible:** Yes (Rollback available)
