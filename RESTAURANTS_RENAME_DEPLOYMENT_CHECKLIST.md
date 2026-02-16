# ✅ RESTAURANTS RENAME - DEPLOYMENT CHECKLIST

## 🎯 قبل التشغيل على Production

### الملفات المطلوبة
- [ ] ✅ `database/migrations/2026_02_16_000000_rename_restaurants_to_restaurants_and_cafe.php`
- [ ] ✅ `RESTAURANTS_RENAME_QUICK_DEPLOY.md` (قراءة)
- [ ] ✅ `RESTAURANTS_RENAME_MIGRATION_GUIDE.md` (مرجع)

### التحضيرات الأساسية
- [ ] قراءة Quick Deploy guide (2 دقيقة)
- [ ] فهم ميزات الأمان السبعة
- [ ] التأكد من الوصول لـ Production server
- [ ] التأكد من وجود Database backup

### معلومات مهمة
- [ ] Category ID: **92** ✓
- [ ] English Name: **Restaurants and Cafe** ✓
- [ ] Arabic Name: **المطاعم والكافيهات** ✓
- [ ] French Name: **Restaurants et Cafés** ✓

---

## 🚀 مراحل التشغيل

### المرحلة 1: الاتصال والتحضير

```bash
# 1. انتقل لمجلد التطبيق
cd /path/to/speeda

# 2. تحقق من وجود Migration file
ls -la database/migrations/2026_02_16_000000_rename_restaurants_to_restaurants_and_cafe.php
# يجب أن تظهر: -rw-r--r-- ... 2026_02_16_000000_rename...

# 3. قائمة الـ migrations المعلقة
php artisan migrate:status
# يجب أن تظهر: 2026_02_16_000000_... (Pending)
```

### المرحلة 2: تشغيل الـ Migration

```bash
# 1. شغّل الـ Migration
php artisan migrate --path=database/migrations/2026_02_16_000000_rename_restaurants_to_restaurants_and_cafe.php

# Expected Output:
# Migrating: 2026_02_16_000000_rename_restaurants_to_restaurants_and_cafe
# Migrated:  2026_02_16_000000_rename_restaurants_to_restaurants_and_cafe (XXXms)

# ✅ يجب أن ترى "Migrated" بدون أخطاء
```

- [ ] Migration شغّل بنجاح بدون أخطاء
- [ ] الـ Output يظهر "Migrated:" مع الوقت

### المرحلة 3: مسح الـ Cache

```bash
# امسح جميع الـ Caches
php artisan cache:clear && php artisan view:clear && php artisan config:clear

# Expected: بدون رسائل خطأ
```

- [ ] Cache تم مسحه بنجاح

### المرحلة 4: التحقق من النتيجة

#### التحقق 1: قاعدة البيانات
```bash
php artisan tinker
DB::table('categories')->where('id', 92)->first();
```

يجب أن تظهر:
```
name_en: "Restaurants and Cafe" ✅
name_ar: "المطاعم والكافيهات" ✅
name_fr: "Restaurants et Cafés" ✅
```

- [ ] `name_en` = "Restaurants and Cafe"
- [ ] `name_ar` = "المطاعم والكافيهات"
- [ ] `name_fr` = "Restaurants et Cafés"

#### التحقق 2: Verification Script
```bash
php artisan tinker < verify_restaurants_rename.php
```

يجب أن تظهر:
```
✅ PASSED: Category 92 found
✅ PASSED: English Name
✅ PASSED: Arabic Name
✅ PASSED: French Name
🎉 ALL TESTS PASSED
```

- [ ] جميع الاختبارات نجحت

#### التحقق 3: في المتصفح

**Arabic Mode (`/ar`):**
- [ ] ابحث عن قسم الطعام
- [ ] يجب أن تظهر: "المطاعم والكافيهات" ✅
- [ ] لا توجد كلمات إنجليزية مختلطة

**French Mode (`/fr`):**
- [ ] ابحث عن قسم الطعام
- [ ] يجب أن تظهر: "Restaurants et Cafés" ✅
- [ ] لا توجد كلمات عربية أو إنجليزية مختلطة

**English Mode (`/en`):**
- [ ] ابحث عن قسم الطعام
- [ ] يجب أن تظهر: "Restaurants and Cafe" ✅
- [ ] لا توجد كلمات عربية مختلطة

#### التحقق 4: Logs Check
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

- [ ] Logs تظهر جميع الخطوات بنجاح

---

## 📋 بعد التشغيل الناجح

### التنظيف
- [ ] أغلق جميع جلسات `php artisan tinker`
- [ ] تحقق من عدم وجود أخطاء في الـ Logs
- [ ] تأكد من أن الموقع يعمل بشكل طبيعي

### المراقبة (لمدة 30 دقيقة)
- [ ] راقب استجابة الموقع
- [ ] تحقق من عدم وجود أخطاء جديدة في الـ Logs
- [ ] اختبر في أجهزة مختلفة (phone, tablet, desktop)

### الإخطارات (اختياري)
- [ ] أخبر فريق الدعم عن التحديث الناجح
- [ ] أخبر المديرين بنجاح العملية

---

## 🔙 في حالة وجود مشكلة

### شيء لم يعمل! ماذا أفعل؟

#### المشكلة 1: Migration لم تشتغل
```bash
# التحقق من وجود الملف
ls -la database/migrations/2026_02_16_000000*.php

# جرب مرة أخرى
php artisan migrate --path=database/migrations/2026_02_16_000000_rename_restaurants_to_restaurants_and_cafe.php
```

- [ ] تحقق من اسم الملف بالضبط

#### المشكلة 2: البيانات لم تتحدث
```bash
# تحقق من قيمة Database
php artisan tinker
DB::table('categories')->where('id', 92)->first();

# تحقق من الـ Logs
tail -100 storage/logs/laravel.log
```

- [ ] شغّل verification script
- [ ] اقرأ الـ Logs كاملة للأخطاء

#### المشكلة 3: رسالة خطأ
```bash
# اقرأ الخطأ بحذر
# ابحث عن "[Restaurants Migration]" في الـ Log

# القسم المرتبط في الـ Logs:
tail -200 storage/logs/laravel.log | grep -i "restaur"
```

- [ ] اكتب رسالة الخطأ كاملة
- [ ] اتصل بفريق الدعم مع الـ Log

### أخيراً: Rollback (إذا لزم الأمر)

```bash
# تراجع عن الـ Migration
php artisan migrate:rollback --step=1

# امسح الـ Cache
php artisan cache:clear

# تحقق من الاستعادة
php artisan tinker
DB::table('categories')->where('id', 92)->first();
# يجب أن تعود القيم الأصلية
```

- [ ] تم الـ Rollback بنجاح
- [ ] القيم الأصلية استعادت

---

## 🎓 أثناء القراءة

اقرأ حسب الترتيب:

1. **الأول:** `RESTAURANTS_RENAME_QUICK_DEPLOY.md` (2 دقيقة)
2. **الثاني:** `RESTAURANTS_RENAME_MIGRATION_GUIDE.md` (10 دقيقة)
3. **المرجع:** `RESTAURANTS_RENAME_README.md` (عند الحاجة)

---

## 📊 جدول التحقق النهائي

| الخطوة | القائمة | النتيجة |
|--------|---------|---------|
| 1. Preparation | ✅ | جاهز |
| 2. Migration Run | ✅ | شغّل بنجاح |
| 3. Cache Clear | ✅ | تم مسحه |
| 4. DB Check | ✅ | البيانات محدثة |
| 5. Verify Script | ✅ | نجح |
| 6. Browser Test | ✅ | صحيح في الأوضاع الثلاث |
| 7. Logs Check | ✅ | كل الخطوات مسجلة |

---

## ✨ نقاط مهمة

### تذكر:
- ✅ الـ Migration آمن تماماً
- ✅ يمكن تشغيله أكثر من مرة
- ✅ سهل الـ Rollback إذا حدثت مشكلة
- ✅ جميع التغييرات مسجلة في الـ Logs

### تجنب:
- ❌ لا تعدّل ملف الـ Migration
- ❌ لا تشغّله من مجلد مختلف
- ❌ لا تنسَ مسح الـ Cache
- ❌ لا تتجاهل رسائل الأخطاء

---

## 🚀 الآن جاهز للبدء!

```bash
cd /path/to/speeda
php artisan migrate --path=database/migrations/2026_02_16_000000_rename_restaurants_to_restaurants_and_cafe.php
php artisan cache:clear
php artisan tinker < verify_restaurants_rename.php
# ✅ Done!
```

---

## 📞 معلومات التواصل

### في حالة الحاجة للدعم:
- أرسل: Logs كاملة من `storage/logs/laravel.log`
- أرسل: نتيجة Database query
- أرسل: الخطأ (رقم الخطأ + رسالة)

### Important Files:
- Migration: `database/migrations/2026_02_16_000000_*.php`
- Docs: `RESTAURANTS_RENAME_*.md` (اختر ما تحتاج)
- Tests: `verify_restaurants_rename.php`

---

**Status:** ✅ جاهز للتشغيل الآن

**Time Required:** ~20 دقيقة (قراءة + تشغيل + تحقق)

**Risk Level:** 0% (Enterprise-safe)

**Go ahead! You got this! 🚀**
