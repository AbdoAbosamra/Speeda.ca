# Feature: Provider Service Areas (أماكن التوفّر المتعددة)

> أُضيفت بتاريخ 2026-06-28 على الفرع `Full-VersionV3`.
> تتيح لمقدّم الخدمة اختيار **مواقع إضافية** متاح فيها لخدمة العملاء (بخلاف موقعه الأساسي
> المسجّل)، فيظهر تلقائيًا في **فلتر المكان** لتلك المناطق مع شارة "متاح في هذه المنطقة".

---

## 1. الفكرة والسلوك

- لكل مقدّم خدمة **موقع أساسي واحد** (`service_providers.location_id`) يُحدَّد عند التسجيل.
- الآن يمكنه إضافة **مواقع إضافية**، تُخزَّن في جدول `service_areas`.
- **مهم — التقييد على مناطق الفلتر:** قائمة الاختيار تعرض **فقط المدن المغطّاة بفلتر المكان العام**
  (المدن المكوّنة للمجموعات الثمانية: Laval, Montréal, Ottawa, Gatineau, Mississauga, Brampton,
  Oakville, Burlington, Milton, Markham, Vaughan, Richmond Hill, Oshawa, Whitby, Ajax, City of Toronto).
  أي مدينة خارج هذه المجموعة لن تظهر في أي فلتر، لذا لا تُعرض ولا تُقبل في التحقق.
  المصدر الموحّد لهذه المدن: `LocationClusterService::getFilterableCityNames()`.
- في صفحة الدليل العامّة `/service-providers`، عند الفلترة بمكان، يظهر المزوّد إذا كان:
  - موقعه الأساسي ضمن المكان المختار، **أو**
  - لديه `service_area` نشط في المكان المختار.
- المزوّد الذي طابق **عبر منطقة خدمة فقط** (موقعه الأساسي مختلف) يظهر بشارة خضراء
  **"Available in this area"** على بطاقته لتمييزه عن المزوّدين المتمركزين هناك.

> ملاحظة: المنطق يحترم نظام الـ **clusters** الحالي (Laval–Montréal، Ottawa–Gatineau،
> ومجموعات GTA) — أي معرّفات المواقع الناتجة عن الـ cluster تُطابَق ضد كل من الموقع
> الأساسي ومناطق الخدمة.

---

## 2. قاعدة البيانات

**لا توجد ميجريشن جديدة** — الجدول موجود مسبقًا:

`service_areas` (من `2025_10_26_143118_enhance_service_provider_business_features.php`):

| العمود | النوع | ملاحظة |
|--------|------|--------|
| service_provider_id | FK → service_providers | cascade |
| location_id | FK → locations | cascade |
| radius_km | int default 10 | افتراضي |
| extra_charge | decimal nullable | — |
| estimated_travel_time | int nullable | — |
| is_active | bool default true | تُستخدم للفلترة |
| **unique** | (service_provider_id, location_id) | يمنع التكرار |
| **index** | (location_id, is_active) | لأداء الفلتر |

العلاقات في `ServiceProvider`:
- `serviceAreas()` → `hasMany(ServiceArea)`
- `locations()` → `belongsToMany(Location, 'service_areas')->withPivot('radius_km','is_active')->withTimestamps()`

---

## 3. الملفات المعدّلة

### `app/Models/ServiceProvider.php`
- `serviceAreaLocationIds(): array` — معرّفات المواقع النشطة للمزوّد (لتعبئة الفورم)، تتفادى N+1 عبر استخدام العلاقة المحمّلة إن وُجدت.
- `scopeAvailableInLocations($query, array $locationIds)` — جوهر الفلتر:
  ```php
  WHERE location_id IN (...) OR EXISTS (service_areas WHERE location_id IN (...) AND is_active = 1)
  ```

### `app/Services/LocationClusterService.php`
- `getFilterableCityNames()` — اتحاد كل مدن مجموعات الفلتر العامّة (المصدر الموحّد للمدن المعتمدة).
- `getFilterableLocationIds()` — معرّفات المواقع النشطة التي مدنها ضمن الفلتر (للتحقق).
- `getFilterableLocations()` — نفس المواقع ككولكشن مرتّب (لعرض قائمة الاختيار).

### `app/Http/Requests/UpdateServiceProviderProfileRequest.php`
- قواعد: `service_areas` (nullable array, max 50)، و`service_areas.*` (integer, distinct, **`Rule::in(getFilterableLocationIds())`**) — أي يُرفض أي موقع خارج مناطق الفلتر.
- رسائل الخطأ + الـ attribute (تستخدم مفاتيح `sp_validation.sp_service_areas_*`).

### `app/Http/Controllers/ServiceProviderController.php`
- **`index()`**: فلتر المكان يستخدم `availableInLocations()` بدل `whereIn('location_id')`؛ يمرّر `$activeLocationIds` للـ view؛ يضيف `withExists(... as is_available_area)` (استعلام فرعي واحد، بلا N+1) لتحديد المطابقة عبر منطقة الخدمة.
- **`show()`**: يحمّل علاقة `serviceAreas` ضمن `loadMissing`؛ يمرّر `$serviceAreaLocationIds` (المختارة حاليًا) و`$serviceAreaLocations` (المدن المعتمدة فقط) للفورم.
- **`update()`**: المزامنة تتقاطع مع `getFilterableLocationIds()` (دفاع في العمق) بالإضافة لاستبعاد الموقع الأساسي.
- **`update()`**: داخل نفس الـ transaction، يزامن `locations()->sync(...)` مع المواقع المختارة (مع `is_active=true`)، **مستبعدًا الموقع الأساسي** (ضمني). يعتمد على حقل مخفي `has_service_areas` للتمييز بين "لا تغيير" و"إلغاء الكل".

### `resources/views/service-providers/show.blade.php`
- بطاقة "Available Service Areas" بتصميم متطابق مع باقي الفورم (Bootstrap card + `custom-checkbox-card` نفس نمط اللغات):
  - شبكة قابلة للتمرير من اختيارات متعددة (checkbox chips) من **`$serviceAreaLocations` (المدن المعتمدة فقط)**، تستبعد الموقع الأساسي.
  - **بحث فوري** (client-side) + **عدّاد محدّد** حيّ.
  - حقل مخفي `has_service_areas`.
- سكربت صغير (بحث + عدّاد) في نهاية قسم المحتوى.

### `resources/views/service-providers/index.blade.php`
- شارة خضراء "Available in this area" تظهر عندما
  `is_available_area && !in_array(location_id, activeLocationIds)`.

### ملفات الترجمة (en/ar/fr)
- `service_provider.php`: `service_areas_section`, `service_areas_hint`, `service_areas_search_placeholder`, `no_locations_available`, `primary_location_badge`, `available_in_area`.
- `sp_validation.php`: `sp_service_areas_invalid`, `sp_service_areas_distinct`, `sp_service_areas_exists`.

---

## 4. حالات حدّية مُعالَجة

| الحالة | السلوك |
|--------|--------|
| اختيار الموقع الأساسي ضمن المناطق | يُستبعَد تلقائيًا (ضمني، لا تكرار) |
| إلغاء كل الاختيارات | يُمسح الكل (بفضل علامة `has_service_areas`) |
| الفورم بدون قسم المناطق (لم يُرسَل) | المناطق الحالية تبقى دون تغيير |
| موقع غير موجود (id خاطئ) | يُرفض بالتحقق، لا شيء يُحفَظ |
| مدينة حقيقية لكن خارج مناطق الفلتر (مثل Vancouver) | لا تُعرض، وتُرفض بالتحقق إن أُرسلت يدويًا |
| مستخدم غير المالك | `authorize()` يفشل، لا شيء يُحفَظ |
| منطقة خدمة `is_active=false` | لا تظهر في الفلتر ولا في الـ helper |

---

## 5. الاختبارات

`tests/Feature/ServiceProvider/ServiceAreaTest.php` — **14 اختبارًا، كلها خضراء (32 assertion)**:

**الحفظ/المزامنة:** حفظ عدة مناطق · استبعاد الموقع الأساسي · إلغاء الكل يمسح · غياب العلامة يحفظ الحالة · رفض id غير صالح · **رفض مدينة خارج مناطق الفلتر** · منع غير المالك.

**الـ Scope/الفلترة:** مطابقة الموقع الأساسي · مطابقة منطقة خدمة نشطة · تجاهل غير النشطة · صحّة `serviceAreaLocationIds()`.

**الدليل العام:** ظهور المزوّد المتاح عبر منطقة خدمة في الفلتر · ظهور الشارة للمطابقة عبر المنطقة فقط · عدم ظهور الشارة بلا فلتر.

> **ملاحظة عن بيئة الاختبار:** الـ migrations تزرع مدن أونتاريو/كيبيك في `locations`
> (و`city` فريد)، لذا تستخدم الاختبارات `firstOrCreate` عبر helper `loc()` لإعادة
> استخدام المدن المزروعة بدل إدخال مكرّر. (هذه برمجية اختبار فقط — `LocationFactory`
> الافتراضي يصطدم بالقيد الفريد، وهي مشكلة سابقة في مجموعة الاختبارات.)

التشغيل:
```bash
php artisan test tests/Feature/ServiceProvider/ServiceAreaTest.php
```

---

## 6. أداء

- الفلتر = شرط `OR EXISTS` مدعوم بالـ index `(location_id, is_active)`.
- شارة التوفّر = `withExists` (استعلام فرعي واحد لكل الصفحة، **بلا N+1**).
- المزامنة عند الحفظ فقط (لا حساب وقت تشغيل على القراءة).
