# 🚀 SPEEDA - Project Master Context (The Source of Truth)

**Version**: 5.5 (Final Refactored State)
**Last Update**: February 2026
**Tech Stack**: Laravel 12, PHP 8.4, Tailwind CSS, Alpine.js, MySQL.

---

## 🎯 Core Philosophy (قواعد ذهبية)

1. **No APIs / No Inertia**: المشروع يعتمد كلياً على (Blade Templates) و (Redirects). لا يتم إرجاع JSON إلا في حالات AJAX بسيطة (مثل عداد الزوار).
2. **Auth Method**: استخدام `Auth::facade` حصراً (مثل `Auth::user()`) لتجنب مشاكل Laravel 11/12 مع الـ helper `auth()`.
3. **Admin Scope**: لوحة التحكم مخصصة فقط لإدارة (الأقسام، المواقع، إحصائيات الزوار، الموافقة على التقييمات). تم حذف إدارة المستخدمين لزيادة الأمان.

---

## 🏗️ Data Architecture (هيكل البيانات الموحد)

### 1. Identity & Profiles (The Merger)

- **User Model**: يمثل الحساب الأساسي (admin, client, service_provider).
- **ServiceProvider Model**: (تم دمج Profile فيه) هو المرجع الوحيد لبيانات المزود (المهنة، الموقع، الصور، حالة التوثيق).
- **Legacy Cleanup**: تم إلغاء `ServiceProviderProfile` القديم لتقليل التعقيد.

### 2. Taxonomy & Location

- **Category**: نظام شجري (Parent/Child) مع توليد تلقائي للـ Slugs.
- **Location**: يدعم التسلسل الهرمي (دولة -> مدينة -> منطقة) مع إحداثيات (Lat/Long) وصورة لكل موقع.

### 3. Feedback System (Moderated)

- **Review**: تقييمات (1-5 نجوم) مرتبطة بـ `ServiceProvider`. لا تظهر إلا بعد `is_active = true` (موافقة الأدمن).
- **Comment**: نظام تعليقات Polymorphic على التقييمات، يخضع أيضاً لموافقة الأدمن.

---

## 🔐 Logic & Business Rules (منطق العمل)

### ✅ Registration Logic (The Fix)

- عند تسجيل (Client): حقل `profession` اختياري (Null).
- عند تسجيل (Service Provider): حقل `profession` إجباري ويتم ربطه بالقسم المناسب.

### ✅ Visitor Tracking (Privacy First)

- **Middleware**: `TrackVisitor` يقوم بتشفير الـ IP و User Agent (Hashing SHA256) قبل الحفظ (GDPR Compliant).
- **Deduplication**: لا يتم احتساب الزيارة المكررة إلا بعد مرور 5 دقائق.

### ✅ Image Management

- **Storage**: جميع الصور تُخزن في `storage/app/public/`.
- **Fallbacks**: يوجد نظام Placeholder تلقائي في حال فقدان الصورة (خاصة للـ SVG واللوجو).

---

## 🛠️ Utility Helpers (أدوات مساعدة)

- **ErrorHelper**: المسؤول عن فلاشات الرسائل (Success/Error) بأسلوب موحد.
- **WhatsAppHelper**: لتسهيل التواصل المباشر مع المزودين.

---

## 🚦 Deployment & Readiness

- **Production State**: 100% Ready.
- **Migrations**: تم إصلاح مشكلة طول أسماء الـ Constraints.
- **Cache**: يتم مسح الكاش تلقائياً (Categories/Locations) عند أي عملية تحديث من الأدمن.
