# Speeda — المرجعية الشاملة المستخرجة من الكود الفعلي

> وثيقة تحليل عميق للمشروع، مبنية بالكامل على قراءة الكود المصدري الفعلي (وليس الوثائق القديمة).
> تاريخ التحليل: 2026-06-27 — الفرع: `Full-VersionV3`.
> الهدف: مرجع واحد يشرح كل طبقة، كل قرار معماري، وكل سلوك فعلي في النظام.

---

## 1. هوية المشروع ونظرة عامة

**Speeda** منصّة (Marketplace / Directory) تربط **مقدّمي الخدمات المحترفين** بالعملاء في كندا (مناطق Québec وOntario/GTA تحديدًا). النموذج ليس نظام حجوزات كامل، بل **دليل مزوّدين + تواصل مباشر عبر WhatsApp/الهاتف** مع طبقة سمعة (تقييمات/مراجعات/توصيات) وتحليلات.

- **المجال الأساسي (Core Entity):** `ServiceProvider` (مقدّم الخدمة) — هو محور كل شيء.
- **النموذج التجاري الفعلي:** المستخدم يتصفّح → يفلتر حسب التصنيف/المدينة → يفتح بروفايل المزوّد → يكشف رقم التواصل (Reveal Contact) → يتواصل عبر WhatsApp. لا توجد مدفوعات أو حجوزات نشطة (جداول `bookings`/`service_packages`/`portfolios` موجودة لكنها شبه معطّلة/Legacy).
- **ثلاث لغات:** الإنجليزية (en) والعربية (ar) والفرنسية (fr)، مع RTL للعربية.
- **التوقيت:** `America/Toronto` (مثبّت في `config/app.php`).

---

## 2. الحزمة التقنية (Stack)

| الطبقة | التقنية | المصدر |
|--------|---------|--------|
| اللغة | PHP ^8.2 | `composer.json` |
| الإطار | Laravel ^12.0 | `composer.json` |
| الواجهة الحية | Livewire ^4.2 + Volt 1.10.5 | `composer.json` |
| الوسائط | spatie/laravel-medialibrary ^11.21 | معرض صور المزوّد |
| SEO | artesaos/seotools ^1.4 + spatie/laravel-sitemap ^8.1 | طبقة Domain/SEO |
| PDF | barryvdh/laravel-dompdf | تصدير تحليلات المزوّد |
| الصور | intervention/image ^3.11 | تحويلات الوسائط |
| الترجمات | outhebox/laravel-translations ^1.4 | إدارة الترجمة |
| API tokens | laravel/sanctum ^4.2 | `personal_access_tokens` |
| MCP | laravel/mcp ^0.3.2 | — |
| الأصول | Vite ^7 + TailwindCSS + Alpine.js ^3 | `package.json`, `resources/css/app.css` |
| الاختبار | PHPUnit ^11.5 + Mockery + Faker | `phpunit.xml` |

> ملاحظة: لا يوجد `laravel/breeze` في الإنتاج (dev only)؛ نظام Auth مبني يدويًا فوق Breeze scaffolding المعدّل.

---

## 3. البنية المعمارية (Layered Architecture)

المشروع يتبع **معمارية طبقية واضحة مع فصل مسؤوليات قوي** (أعلى من متوسط مشاريع Laravel):

```
HTTP Request
  → Middleware (SetLocale, TrackVisitor, CheckUserStatus, admin/role)
  → Routes (web.php / auth.php)
  → FormRequest (التحقق + التفويض)
  → Controller (تنسيق فقط — رقيق نسبيًا)
      → Service / Action (منطق العمل)
      → Filter (بناء استعلامات الفلترة)
      → Domain\SEO\Builders (بناء الميتا)
  → Model (Eloquent + Accessors + Scopes + Observers)
  → View (Blade + Components + i18n)
```

### الطبقات الفعلية في `app/`:
- **`Http/Controllers`** (37 ملف): تنسيق رقيق، تفويض الحسابات للخدمات. مقسّمة إلى عامّة + `Admin/` + `Auth/`.
- **`Services`** (9 خدمات): منطق العمل القابل لإعادة الاستخدام والـ caching.
- **`Actions`** (3): عمليات ذرّية أحادية الغرض (`CalculateProfileCompletionAction`, `TrackProviderViewAction`, `TrackProviderClickAction`).
- **`Domain/SEO`**: نمط Builder + DTO منفصل لكل نوع صفحة.
- **`Filters/ServiceProviderFilter`**: محرك فلترة (مستخدم جزئيًا — انظر القسم 22).
- **`Observers`** (3): `ServiceProviderObserver`, `ReviewObserver`, `CategoryObserver`.
- **`Policies`** (5)، **`Rules`** (2)، **`Traits/LogsAdminActions`**، **`Helpers`** (`ErrorHelper`, `FlashHelper`).

### مبادئ مطبّقة فعليًا:
- **Defense in Depth:** قواعد العمل تُفرض في FormRequest *و* في الكنترولر (مثال: قفل تغيير التصنيف في `ServiceProviderController::update`).
- **N+1 prevention:** `Model::preventLazyLoading()` مفعّل في `local/staging` فقط (`AppServiceProvider`).
- **Graceful degradation:** فحص `Schema::hasColumn` قبل استخدام أعمدة قد لا تكون مهاجَرة بعد (Post, CheckUserStatus, Actions).

---

## 4. دورة حياة الطلب والـ Middleware

التسجيل في `bootstrap/app.php` (Laravel 12 الجديد، بدون `Kernel.php`):

**يُضاف إلى مجموعة `web` بالترتيب:**
1. `SetLocale` — يحدّد اللغة بأولوية: `?lang=` query → session → Accept-Language → fallback (`en`). يحفظ في session.
2. `TrackVisitor` — يسجّل الزوّار على طلبات GET فقط، يتخطّى الأدمن، يجزّئ IP+UserAgent بـ `hash_hmac(sha256, ip, app.key)`، ويمنع التكرار خلال 5 دقائق، ويمسح كاش `visitor_stats`/`live_visitors_count`.
3. `CheckUserStatus` — يسجّل خروج المستخدمين المعطّلين (`is_active=false`) فورًا مع إبطال الجلسة.

**Aliases:**
- `admin` → `AdminMiddleware` (يتطلّب `isAdmin()`).
- `role` → `RoleMiddleware` (مطابقة `role` صريحة).
- `handle.large.uploads` → `HandleLargeUploads`.

**جدولة (Scheduler):** مهمّة يومية تحذف `AdminNotification` المنتهية الصلاحية.

**معالجة الاستثناءات (`withExceptions`):** معالجات مخصّصة تُرجع JSON للطلبات AJAX و redirect+flash للطلبات العادية، لكل من: Validation (422)، Auth (401)، Authorization (403)، CSRF/TokenMismatch (419)، PostTooLarge (413)، ModelNotFound (404)، وعام (500). في وضع debug يتم تجاهل المعالج العام.

---

## 5. المصادقة والأدوار (Auth & RBAC)

### الأدوار الثلاثة (عمود `users.role` نصّي):
- `client` — عميل (تسجيل ببريد+كلمة مرور فقط).
- `service_provider` — مقدّم خدمة (يتطلّب اسم، موبايل كندي، مهنة، مدينة، قبول الشروط).
- `admin` — مدير.

### تحديد الأدمن — مزدوج (`User::isAdmin()`):
```php
role === 'admin'  OR  email ∈ config('auth.admins')
```
قائمة `auth.admins` تُقرأ من `ADMINS` أو `ADMIN_EMAIL` في `.env`. **مهم:** يُستخدم `config('auth.admins')` وليس `env()` مباشرة لتفادي مشاكل config:cache.

### تدفّق التسجيل (`RegisteredUserController::store` → `AuthService::registerUser`):
1. التحقق عبر `RegisterRequest` (قواعد مشروطة حسب الدور).
2. `AuthService` يفتح `DB::transaction`:
   - ينشئ `User` (مع تطبيع المهنة؛ `email_verified_at = now()` للمزوّد فورًا، `null` للعميل).
   - إن كان مزوّدًا: ينشئ سجل `ServiceProvider` مرتبط، مع `getOrCreateLocation()` للمدينة.
3. `event(Registered)` ثم `Auth::login`.
4. إعادة توجيه: المزوّد → صفحة بروفايله، العميل → الرئيسية.

### نقاط مهمّة في `RegisterRequest`:
- مدن التسجيل **مقيّدة** بقائمة `SIGNUP_CITIES` (16 مدينة: Montreal, Laval, Gatineau, Ottawa + 12 مدينة GTA).
- الموبايل وواتساب يُتحقّق منهما عبر `CanadianPhoneNumber` ويجب أن يكونا فريدين في `service_providers`.
- المهنة يجب أن تكون `terminal()` category أو القيمة `'other'`.
- تطبيع الهاتف إلى صيغة `+1XXXXXXXXXX` في `prepareForValidation`.

### صفحة دخول/تسجيل موحّدة:
`GET /login` و`GET /register` يعرضان نفس الصفحة (تبويبات). `routes/auth.php` معدّل بـ throttling: تسجيل `5,1`، دخول `10,1`، تحقق بريد `6,1`.

### قاعدة `CanadianPhoneNumber`:
10 أرقام بعد التطبيع، يزيل رمز الدولة `1`/`+1` والامتدادات، الرقم الأول لا يكون 0 أو 1.

---

## 6. نموذج البيانات (Models & Schema)

16 نموذجًا. الجداول الأساسية والعلاقات:

### `User` (`users`) — SoftDeletes
- حقول: name, email, password, profession, role, is_active.
- علاقات: `serviceProvider` (hasOne)، `bookings` (client_id)، `savedProviders` (belongsToMany عبر `saved_providers`)، `endorsements`، `reviews` (client_id)، `comments`، `readAdminNotifications` (belongsToMany عبر `admin_notification_user` بـ pivot `read_at`).
- دوال: `isAdmin/isClient/isServiceProvider/isActive/assignRole`. Scopes: `active/inactive`.

### `ServiceProvider` (`service_providers`) — **الكيان المحوري** (537 سطر، يطبّق `HasMedia`)
- مفاتيح: `user_id`, `category_id`, `location_id`.
- بيانات العمل: company_name, bio, address, experience_years, hourly_rate, business_type, business_license, certification.
- توفّر: emergency_available, available_weekends, available_evenings, response_time_hours, availability_schedule (array).
- مصفوفات JSON: languages, specializations, services_offered.
- تواصل: phone, whatsapp_number, contact_email.
- حالة/تقييم: is_verified, is_certified, views, **rating**, **calculated_rating**, endorsement_count, profile_completion_percent, profile_completion_popup_shown_at.
- **الوسائط (Spatie):** مجموعة `gallery` (قرص public، JPEG/PNG/WebP، حد 10MB)، تحويلان: `gallery_thumb` (600×600 crop/webp/q80) و`gallery_large` (1200×1200/webp/q85)، كلاهما `nonQueued`.
- صورة البروفايل: عمود `profile_image` منفصل (تخزين يدوي في `profile-images/`، **ليس** عبر Spatie).
- علاقات: user, category, location (belongsTo)، serviceAreas/locations (belongsToMany عبر `service_areas`)، reviews/activeReviews، endorsements.
- Accessors محورية: `localized_company_name/bio/address` (نظام fallback)، `profile_image_url` (مع `normalizePublicUrl` لمسارات نسبية same-origin)، `gallery_image_url`، `display_rating`، `total_reviews_count`، `formatted_views` (K/M).
- `recalculateRating()`: يحدّث **عمودَي** `rating` و`calculated_rating` معًا من المراجعات النشطة.

### `Category` (`categories`) — SoftDeletes, هرمي (426 سطر)
- هرمية ثلاثية: **Section** (parent_id=null, is_section=true) → **Group/FilterGroup** → **Profession (terminal/leaf)**.
- أعمدة لغوية: name/_ar/_en/_fr, description/_ar/_en/_fr، slug, icon, color, sort_order, metadata (array).
- Scopes: `sections, subcategories, filterGroups, terminal, bySection, popular, search, active`.
- دوال شجرية: `descendantAndSelfIds()`, `providerCategoryIds()` (يُرجع الأوراق فقط)، `resolveFilterValue()` (يحلّ id أو slug).
- توليد slug تلقائي فريد في `boot()`.

### `Location` (`locations`)
- city, country, area, latitude, longitude, image, is_active.
- accessor `name` ⇒ يُطابق `city` (توافقية مع القوالب). `translated_name` عبر ملفات `cities.php`/`location.php`.

### `Review` (`service_provider_reviews`) — **اسم الجدول مختلف عن النموذج**
- service_provider_id, client_id, booking_id, rating (1-5), review_text, rating_breakdown (array), is_verified, is_featured, **is_active** (= معتمد), admin_approved_by/at.
- `approve($admin)`/`reject($admin)` يعيدان حساب تقييم المزوّد تلقائيًا.
- `comments` (morphMany) — التعليقات polymorphic على المراجعات.

### `Comment` (`comments`) — SoftDeletes, **polymorphic** (`commentable`)
- is_active (معتمد), is_flagged, approved_by/at, rejection_reason.
- Scopes: active, pending, flagged, rejected. دوال: approve/reject/flag/unflag.

### `Rating` (`ratings`) — تقييم نجمي سريع منفصل عن المراجعة
- service_provider_id, user_id, rating. `boot()` يعيد حساب متوسط المزوّد عند save/delete.
- **تنبيه:** هذا نظام تقييم **ثانٍ** موازٍ لنظام Review (انظر القسم 21).

### `Endorsement` (`endorsements`) — ميزة "Recommend"
- service_provider_id, user_id. توصية واحدة لكل مستخدم لكل مزوّد. العملاء فقط.

### `Post` (`posts`) — SoftDeletes, مدوّنة متعددة اللغات (305 سطر)
- كل حقل SEO/OG/Twitter بثلاث لغات. status (published/scheduled/draft) أو is_published، published_at، allow_indexing، meta_robots، canonical_url، reading_time_minutes.
- يستخدم `Schema::hasColumn` مخزّنًا (`$columnExistsCache`) لدعم schemas مختلفة.
- route key = slug.

### `Visitor` (`visitors`) — `timestamps=false`, تتبّع خصوصية
- ip_hash, user_agent_hash, path, referer, user_id, visited_at. Scopes زمنية: Last7Days/30Days/12Months/Live(15min)/Unique.

### `ProviderAnalytics` (جدول `analytics`)
- provider_id, user_id, action_type (`view`/`click_whatsapp`), session_hash.
- **Global Scope** `exclude_admin_activity`: يستبعد نشاط الأدمن تلقائيًا (إن وُجد عمود user_id، مُتحكّم به عبر كاش `analytics_has_user_id`).

### `AdminLog` (`admin_logs`)
- admin_id, action, model_type, model_id, model_name, changes (array), ip_address, user_agent, is_undone. دالة ساكنة `log()` + accessor `model` لاسترجاع الكيان.

### `AdminNotification` (`admin_notifications`)
- title/message بثلاث لغات، target_type, expires_at. Scope `active` (غير منتهية). pivot `admin_notification_user` للقراءة.

### نماذج Legacy/شبه معطّلة:
`Booking`, `ServiceArea`, `ServicePackage`, `Portfolio` — موجودة لكن غير مستخدمة فعليًا في التدفّقات الحالية. `Booking` يشير إلى `ServiceProviderProfile::class` غير الموجود (انظر القسم 21).

---

## 7. نظام التصنيفات الهرمي (Categories)

ثلاثة مستويات مع منطق ذكي:
- **resolveFilterValue:** يقبل id رقمي أو slug، يُرجع التصنيف النشط.
- **providerCategoryIds():** عند الفلترة بقسم/مجموعة، يُرجع **معرّفات الأوراق فقط** (terminal) ضمن الشجرة — لأن المزوّدين مرتبطون دائمًا بأوراق. هذا يجعل فلتر "قسم كامل" يجلب كل المهن تحته.
- **التخزين المؤقت:** كل استعلامات الشجرة تمرّ عبر `CategoryCacheService` (TTL 24 ساعة، Redis-first مع fallback، مفاتيح لكل لغة).
- تصنيف "Others" خاص: يُستخدم للمهن غير المصنّفة، وهو الوحيد الذي يُسمح بتغيير تصنيف المزوّد منه (قاعدة قفل التصنيف).
- إعادة توجيه 301: `construction-services` → `renovation-construction` (في الكنترولر).

---

## 8. المواقع والـ Location Clustering

`LocationClusterService` — منطق تجميع المدن للفلترة:

### مجموعتان من الـ clusters:
- **`CLUSTERS`** (ثنائية الاتجاه على مستوى المدينة): Laval↔Montreal، Gatineau↔Ottawa، ومجموعات GTA.
- **`NAMED_CLUSTERS` + `PUBLIC_FILTER_CLUSTERS`** (8 خيارات في قائمة الفلتر العامّة):
  - Laval–Montréal، Ottawa–Gatineau، Mississauga، Brampton، Oakville–Burlington–Milton، Markham–Vaughan–Richmond Hill، Oshawa–Whitby–Ajax، City of Toronto.

### آلية الفلترة (في `ServiceProviderController::index`):
- إذا كانت القيمة مفتاح cluster مسمّى → `getClusterIdsByKey()`.
- إذا كانت رقمية → `getClusterIds()` (يوسّعها للـ cluster) إلا لو وُجد `exact_location` (يتجاوز التجميع — مستخدم من الرئيسية).
- النتيجة: `whereIn('location_id', $ids)`. كاش 6 ساعات لكل cluster.

---

## 9. مقدّمو الخدمة — التدفّقات التفصيلية

### `index` (الدليل العام `/service-providers`):
- SEO حسب السياق (search/category/default).
- Eager loading كامل: `user, category.parent.parent, location, media` + `withCount(activeReviews, endorsements)` + `withExists(is_endorsed)` للعميل (منع N+1).
- يُظهر فقط مزوّدي المستخدمين النشطين.
- فلاتر: search (company_name/bio/services/user.name)، category (عبر providerCategoryIds)، location (clusters).
- **ترتيب:** من له صورة بروفايل أولًا → `calculated_rating` تنازلي → views تنازلي.
- ترقيم 12/صفحة مع `withQueryString`.

### `show` (بروفايل عام `/service-providers/{id}`):
- يطبّق SEO `provider`.
- يعيد 404 إن كان المستخدم معطّلًا.
- **زيادة المشاهدات** فقط لغير المالك وغير الأدمن، عبر `DB::increment` مباشر + `TrackProviderViewAction` (deduplication بـ session fingerprint خلال 24 ساعة).
- يحسب **إحصائيات المراجعات** (توزيع النجوم 1-5 بنِسب) باستعلام `selectRaw` واحد قبل eager loading.
- يجلب معرض الصور (thumb+large URLs آمنة same-origin)، مراجعات مرقّمة (5/صفحة، باراميتر `reviews_page`)، مزوّدين مشابهين (4 من نفس التصنيف).
- يتحقّق: هل كشف المستخدم التواصل؟ هل راجع؟ هل قيّم؟

### `revealContact` (`POST .../reveal-contact`, throttle 5/1):
- يخزّن معرّف المزوّد في `session('revealed_contacts')` — **خصوصية:** كل مستخدم يرى ما كشفه هو فقط.

### `update`/`updateProfile` (throttle 10/1):
- داخل `DB::transaction`.
- **قفل التصنيف (Defense in Depth):** التصنيف لا يتغيّر إلا إذا كان الحالي = "Others" (يُفرض في FormRequest *و* هنا).
- يعالج services_offered (نص مفصول بفواصل → array).
- رفع معرض الصور عبر Spatie (إضافي)، ثم إعادة حساب نسبة الإكمال.

### `uploadProfileImage` (AJAX):
- تخزين يدوي في `profile-images/` (ليس Spatie)، حذف القديمة، تحديث `profile_image`، إعادة حساب الإكمال.

### معرض الصور (مساران):
- `ServiceProviderController` (delete/replace) — يُرجع redirect.
- `GalleryController` (store/update/destroy) — يُرجع JSON (AJAX). كلاهما محمي بـ throttling.

### حساب نسبة الإكمال (`CalculateProfileCompletionAction`):
صورة بروفايل **40%** + سنوات خبرة 20% + عنوان 20% + خدمات 20%. يُستدعى عبر `ServiceProviderObserver` على created/updated بـ `updateQuietly` (تفادي حلقة لا نهائية).

---

## 10. نظام السمعة (Reputation): مراجعات + تقييمات + توصيات + تعليقات

هذا أكثر الأنظمة تعقيدًا، ويحتوي **ازدواجية مقصودة**:

### أ) المراجعات (Reviews) — `service_provider_reviews`
- العميل ينشئ مراجعة (نجوم + نص) → `is_active=false` (بانتظار اعتماد الأدمن).
- الأدمن يعتمد/يرفض عبر `AdminReviewController` → `Review::approve/reject` → يعيد حساب التقييم.
- `ReviewObserver` يعيد حساب `calculated_rating` على created/updated(is_active|rating)/deleted/restored.
- **مصدر الحقيقة لتقييم المزوّد المعروض.**

### ب) التقييمات السريعة (Ratings) — `ratings`
- تقييم نجمي مباشر بلا نص ولا اعتماد، عبر `RatingController`.
- `Rating::boot()` يحدّث عمود `rating` (فقط) في المزوّد.
- **ازدواجية:** كلا النظامين يكتبان على أعمدة تقييم المزوّد (Review يكتب rating+calculated_rating، Rating يكتب rating) — مصدر تضارب محتمل (انظر القسم 21).

### ج) التوصيات (Endorsements) — "Recommend"
- toggle عبر `EndorsementController` (auth)، عدّاد `endorsement_count`، فحص `isEndorsedBy`.

### د) التعليقات (Comments) — polymorphic
- مرتبطة بـ Reviews (morphMany). تمرّ بدورة اعتماد أدمن (is_active) + flag/unflag.
- `CommentController` (CRUD + flag) للمستخدم، `AdminCommentController` للإدارة.

> **خلاصة عرض التقييم:** `display_rating = calculated_rating ?? rating ?? 0`. الصفحة العامّة تحسب التوزيع مباشرة من `service_provider_reviews` النشطة.

---

## 11. نظام التعدّد اللغوي (i18n)

نظام هجين على ثلاث مستويات:

### المستوى 1 — أعمدة لغوية في DB (محتوى ديناميكي):
أعمدة `*_ar/_en/_fr` في Category, Post, AdminNotification, وبعض حقول ServiceProvider. كل نموذج له `getLocalizedColumn()` بسلسلة fallback موحّدة:
```
locale الحالي → العمود الأساسي → en → ar → fr → ''
```
تُعرض عبر accessors في `$appends` (مثل `localized_name`, `localized_company_name`).

### المستوى 2 — ملفات الترجمة (نصوص الواجهة):
30 ملفًا في `lang/{en,ar,fr}/` (admin, auth, home, categories, cities, reviews, service_provider, validation...). Category وLocation يستعملان `cities.php`/`categories.php` كـ fallback ثانٍ.

### المستوى 3 — اختيار اللغة:
- `SetLocale` middleware (أولوية: `?lang=` → session → المتصفّح → en).
- `LocaleController`: update/switch/getCurrentLocale.
- `config('app.supported_locales')` يحمل الاسم/العلم/الاتجاه، ويُشارَك عبر `View::share`.

---

## 12. نظام التحليلات (Analytics) والخصوصية

### جدول `analytics` (مرن schema):
يسجّل `view` و`click_whatsapp` لكل مزوّد. `TrackProviderViewAction`/`TrackProviderClickAction` يبنيان payload **متوافقًا مع أي schema** (يفحص الأعمدة الموجودة عبر `Schema::getColumnListing` مخزّنًا).

### الخصوصية (مطبّقة فعليًا):
- **لا تُخزَّن عناوين IP خام إطلاقًا.** البصمة = `sha256(analytics_salt + sessionId + '|' + userAgent)`.
- المشاهدات تُزال تكراراتها خلال 24 ساعة (`Cache::add`).
- **نشاط الأدمن مستبعد** على مستويين: في الـ Actions (تخطّي مبكر) + Global Scope على النموذج + `whereNotIn(adminIds)` في الاستعلامات المجمّعة.
- زوّار `Visitor` يُجزَّأون بـ `hash_hmac` بمفتاح التطبيق (GDPR-friendly).

### استهلاك التحليلات:
- **لوحة الأدمن** (`AdminController::dashboard`): نقرات WhatsApp يومي/أسبوعي/شهري مع نسب الاتجاه، أكثر تصنيف نقرًا، أفضل 5 مزوّدين بمعدّل التحويل (clicks/views) آخر 30 يومًا، + إحصائيات زوّار بتوقيت كندا.
- **لوحة المزوّد** (`ProviderDashboardAnalyticsService`): مشاهدات اليوم/الأسبوع، إجمالي النقرات، معدّل التفاعل، اتجاهات يومية (7 أيام)، إحصائيات شهرية (30 يوم). كاش 10 دقائق للبيانات الماضية.
- **تصدير PDF** عبر `ProviderAnalyticsExportController` (dompdf).

---

## 13. لوحة الأدمن (Admin Panel)

تحت `/admin` (middleware `auth`+`admin`)، الوحدات:
- **Dashboard:** إحصائيات شاملة (زوّار، مستخدمون، مزوّدون، مراجعات معلّقة، مدوّنات، إشعارات، تحليلات WhatsApp).
- **Categories:** CRUD كامل + toggle status (يبطل كاش التصنيفات).
- **Users:** قائمة، تعطيل/تفعيل، حذف ناعم، **سلّة محذوفات** (trash)، استرجاع، حذف نهائي.
- **Locations:** CRUD + activate/deactivate (FormRequests مخصّصة).
- **Reviews:** workflow اعتماد/رفض/تمييز/إلغاء تمييز/حذف.
- **Comments:** اعتماد/رفض/flag/unflag/حذف/استرجاع.
- **Blog (CMS):** `resource` كامل (عدا show) لإدارة المقالات.
- **Notifications:** إنشاء/حذف إشعارات للمزوّدين.
- **Visitors Analytics:** قراءة فقط + live-count + export.
- **Provider Activity Monitor:** مراقبة نقرات WhatsApp والمشاهدات لكل مزوّد.
- **Activity Logs + Undo:** سجلّ كل إجراءات الأدمن (`AdminLog` + `LogsAdminActions` trait) مع إمكانية التراجع (`UndoController`).
- **Clear Cache:** زرّ لمسح الكاش فورًا.

---

## 14. لوحة مقدّم الخدمة

- `/service-providers/dashboard` → `ProviderDashboardController` يستهلك `ProviderDashboardAnalyticsService`.
- رسوم بيانية (مشاهدات/نقرات يومية)، معدّل تفاعل، تصدير PDF.
- نوافذ منبثقة لتحفيز إكمال البروفايل: `profile-completion-popup`, `profile-completion-banner`, `profile-completion-notification-center` (مكوّنات Blade)، مع dismiss عبر session أو عمود `profile_completion_popup_shown_at`.

---

## 15. نظام SEO (طبقة Domain منفصلة)

تصميم نظيف بنمط **Builder + DTO + Service**:
- **`SeoMetaService`:** نقطة الدخول `apply($type, $model)`. يخزّن النتائج (`SeoData`) في الكاش ساعة واحدة بمفتاح `seo_meta_{type}_{id}_{locale}`، ويحقنها في `SEOTools` (title, description, keywords, canonical, robots, OpenGraph, Twitter, JSON-LD, hreflang).
- **`SeoData` (DTO):** title, description, keywords, ogImage, ogType, canonical, hreflangs, robots.
- **Builders** (لكل نوع صفحة): Home, Category, Provider, Search, BlogIndex, BlogPost — تمتدّ `BaseSeoBuilder` (canonical + hreflangs).
- **إبطال الكاش:** `CategoryObserver` و`ServiceProviderObserver` يستدعيان `invalidate()` تلقائيًا.
- **Sitemap:** `SitemapService` + أمر `GenerateSitemap` (spatie/laravel-sitemap).

---

## 16. استراتيجية التخزين المؤقت (Caching)

نهج **Redis-first مع fallback متدرّج** ومفاتيح واعية باللغة:

| الكاش | TTL | المصدر |
|-------|-----|--------|
| شجرة/مجموعات/أوراق التصنيفات (لكل لغة) | 24 ساعة | `CategoryCacheService` |
| المواقع | 24 ساعة | `LocationCacheService` |
| Location clusters | 6 ساعات | `LocationClusterService` |
| ميتا SEO | 1 ساعة | `SeoMetaService` |
| إحصائيات الرئيسية / المدوّنات | 1 ساعة | `HomeController` |
| إشعارات شريط التنقّل | 5 دقائق | `AppServiceProvider` view composer |
| بيانات لوحة المزوّد الماضية | 10 دقائق | `ProviderDashboardAnalyticsService` |
| معرّفات الأدمن | 1 ساعة | متعدّد |
| أعمدة جدول analytics | للأبد (rememberForever) | Tracking Actions |

`CategoryCacheService::rememberWithFallback`: يحاول Redis → الكاش الافتراضي → استعلام DB مباشر، مع تسجيل تحذيرات بدل الانهيار.

---

## 17. المدوّنة (Blog)

- عامّة: `BlogController` (index/show بـ slug)، `Post::published()` (يدعم published+scheduled)، بحث `searchPublic`.
- إدارة: `Admin/BlogPostController` (resource).
- متعددة اللغات بالكامل (عنوان/محتوى/مقتطف/SEO/OG/Twitter لكل لغة).
- صور: `image_url` accessor مع fallback إلى `images/banner.png`.
- وقت القراءة، التمييز، التحكّم في الفهرسة (allow_indexing/meta_robots/canonical).

---

## 18. الإشعارات

- **للمزوّدين:** `NotificationController` (index, mark-as-read, unread-count). إشعارات الأدمن تُعرض في شريط التنقّل (view composer، كاش 5 دقائق).
- **حالة القراءة:** pivot `admin_notification_user` (read_at).
- **تنظيف تلقائي:** scheduler يومي يحذف المنتهية.

---

## 19. الأمان (Security)

نقاط القوة المرصودة في الكود:
- **Throttling** على كل المسارات الحسّاسة (تسجيل، دخول، reveal-contact، رفع/حذف الصور، analytics، تحقق البريد).
- **Authorization مزدوج:** Policies + فحوص يدوية في الكنترولر + قواعد في FormRequest.
- **CSRF** افتراضي + endpoint `/csrf-token` + معالج 419 ودود يُرجع token جديد.
- **خصوصية البيانات:** لا IP خام، تجزئة بـ HMAC، استبعاد الأدمن من التحليلات.
- **حماية الرفع:** فحص MIME وحجم في الواجهة (FormRequest) *و* الخلفية (`acceptsFile` في Spatie 10MB).
- **Soft deletes** على Users/Posts/Comments/Categories مع سلّة محذوفات واسترجاع.
- **قيود فريدة على الهاتف/الهوية** (migration `add_unique_phone_identity_constraints`).
- **same-origin URLs:** `normalizePublicUrl` يحوّل روابط التخزين إلى مسارات نسبية.

---

## 20. الاختبارات (Tests)

- `phpunit.xml` يعرّف مجموعتي Unit وFeature، DB اختبار، وبيئة معزولة.
- اختبارات Feature موجودة لـ: تسجيل المزوّد، التسجيل العام (المعدّلة في الفرع الحالي).
- Factories لمعظم النماذج (10 factories) + Seeders (14 seeder) بما فيها CategorySeeder وLocationSeeder وProductionCategorySeeder.
- `run-tests.sh`/`run-tests.bat` للتشغيل، أمر `TestDataCleanupCommand` للتنظيف.

---

## 21. القضايا والمخاطر المعروفة (مؤكَّدة من الكود)

> هذه ليست تخمينات — كلّها مرصودة مباشرة في الكود الحالي.

1. **`FacebookConversionService` مرجع ميّت (Dead Reference):**
   مستخدَم في `ServiceProviderController` (3 مواضع) و`RegisteredUserController`، لكن **لا يوجد ملف للكلاس** (أُزيل ضمن "إزالة Meta Pixel"). كل الاستدعاءات ملفوفة بـ `try/catch (\Throwable)` فتفشل صامتةً — أي أن تتبّع CAPI **معطّل فعليًا** بينما الكود يوحي بأنه يعمل. يجب إزالة هذه الاستدعاءات أو إعادة إنشاء الخدمة.

2. **ازدواجية نظام التقييم:** نظامان (`Review` و`Rating`) يكتبان على أعمدة تقييم المزوّد. `Review::recalculateRating` يكتب `rating`+`calculated_rating`، بينما `Rating::recalculateProviderRating` يكتب `rating` فقط بمتوسط مختلف — تضارب محتمل في القيمة المعروضة.

3. **`Booking` يشير إلى `ServiceProviderProfile::class` غير الموجود:** النموذج حُذف (دُمج في ServiceProvider) لكن العلاقة `serviceProviderProfile()` باقية. تعمل فقط لأنها غير مستدعاة.

4. **`ServiceProviderFilter` شبه يتيم:** يستخدم أعمدة/scopes قديمة (`average_rating`, `profession`, `availableWeekends`...) غير موجودة على النموذج الحالي. الفلترة الفعلية تتم داخل `ServiceProviderController::index` مباشرة، لا عبر هذا الفلتر.

5. **`scopeVerified` فارغ:** `ServiceProvider::scopeVerified` يُرجع الاستعلام كما هو (no-op) — أي اعتماد عليه لا يفلتر شيئًا.

6. **فوضى التوثيق:** الجذر و`_Project_Docs/` يحتويان 150+ ملف md/txt متداخل ومتكرّر (تقارير قديمة، ملفات SEO فارغة 0 بايت). يصعب تمييز المصدر الموثوق — لذا أُنشئت هذه الوثيقة من الكود مباشرة.

7. **ملفات تجريبية في الجذر:** `test_*.php`, `test_*.html`, `_fix_w3.php`, `dump_cats.php`, `categories_dump.json` متروكة في الجذر — يُفضّل نقلها/حذفها قبل الإنتاج.

8. **`web.php.bak`** موجود بجانب `web.php` — ملف نسخة احتياطية لا يجب أن يُرفع.

---

## 22. خريطة المسارات الكاملة (Routes)

### عامّة:
- `/` Home — `/service-providers` (index) — `/service-providers/{id}` (show)
- `/categories`, `/categories/{slug}` — `/locations` — `/blogs`, `/blogs/{slug}`
- صفحات ثابتة: privacy-policy, terms-of-service, help-center, legal-affairs, about-us
- locale: POST `/locale`, GET `/locale/{locale}`, `/current-locale`
- `POST .../reveal-contact` (throttle 5/1)، `POST .../analytics/click` (throttle 20/1)
- `GET /csrf-token`

### Auth (`routes/auth.php`): register, login(→register), logout, forgot/reset password, verify-email, confirm-password.

### مصادَق عليه:
- `/dashboard` (يوجّه الأدمن للوحته، غيره للدليل)
- بروفايل المستخدم: edit/update/destroy
- مزوّد: dashboard, analytics/export-pdf, profile (CRUD)، image-upload، gallery (store/update/destroy/replace)، popup dismiss.
- مراجعات (create/store/edit/update/destroy)، تقييمات (rate/my-rating)، توصيات (endorse/toggle)، تعليقات (CRUD+flag)، إشعارات.

### أدمن (`/admin`, auth+admin): dashboard, activity-logs, undo, blog/posts (resource), categories, users(+trash/restore/force), locations, visitors(+live/export), provider-activity-monitor, clear-cache, reviews(workflow), comments(workflow), notifications.

---

## 23. مرجع سريع للملفات المحورية

| الغرض | الملف |
|-------|-------|
| نقطة الدخول/Middleware/الاستثناءات | `bootstrap/app.php` |
| المسارات | `routes/web.php`, `routes/auth.php`, `routes/console.php` |
| الكيان المحوري | `app/Models/ServiceProvider.php` |
| منطق التسجيل | `app/Services/AuthService.php` + `app/Http/Requests/Auth/RegisterRequest.php` |
| الدليل والفلترة | `app/Http/Controllers/ServiceProviderController.php` |
| التصنيفات الهرمية | `app/Models/Category.php` + `app/Services/CategoryCacheService.php` |
| تجميع المدن | `app/Services/LocationClusterService.php` |
| السمعة | `Review.php`, `Rating.php`, `Endorsement.php`, `Comment.php` + Observers |
| التحليلات | `app/Actions/TrackProvider*Action.php` + `ProviderDashboardAnalyticsService.php` |
| SEO | `app/Domain/SEO/**` |
| التسجيل المركزي للـ Observers/Composers | `app/Providers/AppServiceProvider.php` |
| إعدادات اللغة/الأدمن | `config/app.php`, `config/auth.php` |

---

*نهاية المرجع. أُنشئت هذه الوثيقة بقراءة الكود المصدري الفعلي طبقةً بطبقة، وتُعدّ المصدر الموثوق الأحدث لفهم سلوك النظام الفعلي.*
