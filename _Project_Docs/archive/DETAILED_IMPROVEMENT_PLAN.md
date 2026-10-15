# خطة الإصلاح والتحسين - Speeda

**التاريخ:** 15 ديسمبر 2025  
**المدة المقترحة:** 4 أسابيع  
**الأولويات:** من الأعلى إلى الأقل

---

## المرحلة الأولى: إصلاح الأخطاء الحرجة (الأسبوع 1)

### 🔴 المشكلة #1: SetLocale Middleware Error

**الملف:** `app/Http/Middleware/SetLocale.php`

**الكود الحالي (خاطئ):**
```php
public function handle($request, Closure $next)
{
    // ❌ خطأ: محاولة الوصول للـ Session مباشرة
    $locale = $request->session()->get('locale', config('app.locale'));
    // ...
}
```

**الحل المقترح:**
```php
public function handle($request, Closure $next)
{
    // ✅ التحقق من وجود Session أولاً
    if ($request->hasSession()) {
        $locale = $request->session()->get('locale', config('app.locale'));
    } else {
        $locale = config('app.locale');
    }
    
    app()->setLocale($locale);
    
    return $next($request);
}
```

**المدة:** 30 دقيقة  
**الاختبار:** تغيير اللغة من خلال واجهة المستخدم

---

### 🔴 المشكلة #2: نموذج ServiceProviderProfile المكرر

**الملفات المتأثرة:**
- `app/Models/ServiceProviderProfile.php`
- `database/migrations/2025_10_08_000004_create_service_provider_profiles_table.php`
- جميع المراجع في الـ Controllers

**الخطة:**

**الخطوة 1:** تحديد جميع الأعمدة المهمة في ServiceProviderProfile
```sql
SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_NAME = 'service_provider_profiles'
```

**الخطوة 2:** دمج الأعمدة المفقودة في ServiceProvider
```php
// إضافة migration جديد:
Schema::table('service_providers', function (Blueprint $table) {
    if (!Schema::hasColumn('service_providers', 'completed_jobs')) {
        $table->integer('completed_jobs')->default(0);
    }
    if (!Schema::hasColumn('service_providers', 'portfolio_images')) {
        $table->json('portfolio_images')->nullable();
    }
    if (!Schema::hasColumn('service_providers', 'portfolio_videos')) {
        $table->json('portfolio_videos')->nullable();
    }
    if (!Schema::hasColumn('service_providers', 'is_featured')) {
        $table->boolean('is_featured')->default(false);
    }
    if (!Schema::hasColumn('service_providers', 'certifications')) {
        $table->json('certifications')->nullable();
    }
});
```

**الخطوة 3:** نقل البيانات
```php
// إذا كانت هناك بيانات في service_provider_profiles
DB::statement('
    UPDATE service_providers sp
    JOIN service_provider_profiles spp ON sp.user_id = spp.user_id
    SET sp.completed_jobs = spp.completed_jobs,
        sp.portfolio_images = spp.portfolio_images
    WHERE sp.id IS NOT NULL
');
```

**الخطوة 4:** حذف نموذج و جدول قديم
```php
// في ServiceProvider Model, أضف:
public function serviceProviderProfile()
{
    // للتوافقية مؤقتاً
    return null;
}
```

**الخطوة 5:** تحديث Booking Model
```php
// قبل:
public function serviceProviderProfile()
{
    return $this->belongsTo(ServiceProviderProfile::class);
}

// بعد:
public function serviceProvider()
{
    return $this->belongsTo(ServiceProvider::class);
}
```

**المدة:** 2 ساعة  
**الاختبار:** التحقق من أن جميع Bookings تشير للـ ServiceProvider الصحيح

---

### 🔴 المشكلة #3: ملفات الاختبار في الجذر

**الملفات:**
```
- test-language.php
- test-whatsapp-methods.html
- test-main-nav.html
- public/test-whatsapp.html
```

**الحل:**
```bash
# نقل الملفات:
mkdir -p tests/temporary
mv test-language.php tests/temporary/
mv test-whatsapp-methods.html tests/temporary/
mv test-main-nav.html tests/temporary/
mv public/test-whatsapp.html tests/temporary/
```

**المدة:** 15 دقيقة

---

## المرحلة الثانية: تحسينات البنية (الأسبوع 2)

### 🟡 المشكلة #1: Custom Exception Classes

**الملفات المطلوبة:**

**1. `app/Exceptions/ServiceProviderNotFoundException.php`**
```php
<?php

namespace App\Exceptions;

use Exception;

class ServiceProviderNotFoundException extends Exception
{
    public function __construct($providerId)
    {
        parent::__construct(
            __('exceptions.service_provider_not_found', 
               ['id' => $providerId])
        );
    }
    
    public function render($request)
    {
        return response()->view('errors.not-found', [], 404);
    }
}
```

**2. `app/Exceptions/UnauthorizedProfileAccessException.php`**
```php
<?php

namespace App\Exceptions;

use Exception;

class UnauthorizedProfileAccessException extends Exception
{
    public function __construct()
    {
        parent::__construct(
            __('exceptions.unauthorized_access')
        );
    }
    
    public function render($request)
    {
        return response()->view('errors.unauthorized', [], 403);
    }
}
```

**3. `app/Exceptions/BookingConflictException.php`**
```php
<?php

namespace App\Exceptions;

use Exception;

class BookingConflictException extends Exception
{
    public function __construct($message = null)
    {
        parent::__construct(
            $message ?? __('exceptions.booking_conflict')
        );
    }
    
    public function render($request)
    {
        return response()->json([
            'error' => $this->message
        ], 409);
    }
}
```

**المدة:** 1 ساعة

---

### 🟡 المشكلة #2: تقسيم ServiceProviderController

**الخطة:**

**قبل:** 1 Controller بـ 508 سطر

**بعد:** عدة Controllers صغيرة
```
✓ ServiceProviderController       (البحث والعرض)
✓ ServiceProviderProfileController (الملف الشخصي)
✓ ServiceProviderPortfolioController (الصور والفيديو)
```

**المدة:** 2 ساعة  
**الاختبار:** تشغيل جميع المسارات والتأكد من عملها

---

### 🟡 المشكلة #3: إضافة Request Classes

**الملفات المطلوبة:**

```php
// app/Http/Requests/CreateBookingRequest.php
class CreateBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'service_provider_id' => 'required|exists:service_providers,id',
            'preferred_date' => 'required|date|after:today',
            'service_description' => 'required|string|min:10|max:500',
        ];
    }
}
```

**المدة:** 1 ساعة

---

## المرحلة الثالثة: إضافة الميزات الناقصة (الأسبوع 3-4)

### ✨ الميزة #1: نظام الحجوزات

**الملفات المطلوبة:**

**1. Migration:**
```php
// database/migrations/2025_12_15_000001_improve_bookings_table.php
Schema::table('bookings', function (Blueprint $table) {
    // تحديث الحقول الحالية
    $table->foreign('service_provider_id')
          ->references('id')
          ->on('service_providers')
          ->onDelete('cascade');
});
```

**2. Service Class:**
```php
// app/Services/BookingService.php
class BookingService
{
    public function createBooking(array $data): Booking
    {
        // التحقق من التضارب مع الحجوزات الموجودة
        if ($this->hasConflictingBooking($data)) {
            throw new BookingConflictException();
        }
        
        return Booking::create($data);
    }
    
    private function hasConflictingBooking(array $data): bool
    {
        return Booking::where('service_provider_id', $data['service_provider_id'])
                      ->where('confirmed_date', $data['preferred_date'])
                      ->exists();
    }
}
```

**المدة:** 3 ساعات

---

### ✨ الميزة #2: نظام التقييمات

**الملفات المطلوبة:**

**1. Model:**
```php
// app/Models/Review.php
class Review extends Model
{
    protected $fillable = [
        'service_provider_id',
        'client_id',
        'booking_id',
        'rating',
        'comment',
        'verified_purchase',
    ];

    public function serviceProvider()
    {
        return $this->belongsTo(ServiceProvider::class);
    }

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }
}
```

**2. Service Class:**
```php
// app/Services/ReviewService.php
class ReviewService
{
    public function createReview(array $data): Review
    {
        $review = Review::create($data);
        
        // تحديث التقييم العام للمزود
        $this->updateProviderRating($data['service_provider_id']);
        
        return $review;
    }
    
    private function updateProviderRating($providerId): void
    {
        $rating = Review::where('service_provider_id', $providerId)
                        ->avg('rating');
        
        ServiceProvider::find($providerId)->update(['rating' => $rating]);
    }
}
```

**المدة:** 3 ساعات

---

## المرحلة الرابعة: تحسينات الأداء والأمان

### ⚡ الأداء:

**1. إضافة Caching:**
```php
// app/Services/CategoryService.php
class CategoryService
{
    public function getAllCategories()
    {
        return Cache::remember('categories.all', now()->addDay(), function () {
            return Category::where('is_active', true)->get();
        });
    }
}
```

**2. Eager Loading:**
```php
// في Controllers
$providers = ServiceProvider::with(['user', 'category', 'location', 'reviews'])
                            ->paginate(12);
```

**المدة:** 2 ساعة

---

### 🔒 الأمان:

**1. Rate Limiting:**
```php
// routes/web.php
Route::post('/service-providers/{id}/reveal-contact', [...])
    ->middleware('throttle:5,1')
    ->name('service-providers.reveal-contact');
```

**2. Validation:**
```php
// تحسين الـ Validation في جميع Request Classes
public function rules()
{
    return [
        'email' => 'required|email|unique:users',
        'phone' => 'required|regex:/^[0-9\+\-\s\(\)]+$/',
        'profile_image' => 'file|image|max:2048',
    ];
}
```

**المدة:** 1 ساعة

---

## 📋 قائمة المراجعة (Checklist)

### الأسبوع الأول:
- [ ] إصلاح SetLocale Middleware
- [ ] دمج ServiceProvider مع ServiceProviderProfile
- [ ] نقل ملفات الاختبار من الجذر
- [ ] تشغيل الاختبارات التلقائية

### الأسبوع الثاني:
- [ ] إضافة Custom Exception Classes
- [ ] تقسيم ServiceProviderController
- [ ] إضافة Request Classes
- [ ] إضافة Resource Classes

### الأسبوع الثالث-الرابع:
- [ ] تطوير نظام الحجوزات الكامل
- [ ] تطوير نظام التقييمات والتعليقات
- [ ] إضافة Service Classes المفقودة
- [ ] إضافة Tests

### بعد الانتهاء:
- [ ] مراجعة التغييرات
- [ ] اختبار جميع الميزات
- [ ] وثائق التحديثات
- [ ] نشر الإصلاحات

---

## 🧪 استراتيجية الاختبار

**1. Unit Tests:**
```php
// tests/Unit/Services/BookingServiceTest.php
class BookingServiceTest extends TestCase
{
    public function test_create_booking_with_conflict()
    {
        $this->expectException(BookingConflictException::class);
        // ...
    }
}
```

**2. Feature Tests:**
```php
// tests/Feature/BookingTest.php
class BookingTest extends TestCase
{
    public function test_user_can_create_booking()
    {
        $response = $this->post('/bookings', [...]);
        $response->assertStatus(201);
    }
}
```

---

## 📊 متوازيات وتبعيات

```
┌─────────────────────────────────────┐
│ الأسبوع 1: الأخطاء الحرجة          │
│ ├─ SetLocale Middleware             │
│ ├─ دمج النماذج                      │
│ └─ حذف الملفات المؤقتة              │
└─────────────────────────────────────┘
              ↓
┌─────────────────────────────────────┐
│ الأسبوع 2: تحسينات البنية            │
│ ├─ Custom Exceptions                │
│ ├─ تقسيم Controllers                │
│ └─ إضافة Request Classes            │
└─────────────────────────────────────┘
              ↓
┌─────────────────────────────────────┐
│ الأسبوع 3: الميزات الناقصة          │
│ ├─ نظام الحجوزات                    │
│ ├─ نظام التقييمات                   │
│ └─ Service Classes                  │
└─────────────────────────────────────┘
              ↓
┌─────────────────────────────────────┐
│ الأسبوع 4: الأداء والأمان          │
│ ├─ Caching                          │
│ ├─ Rate Limiting                    │
│ └─ Tests                            │
└─────────────────────────────────────┘
```

---

## 📚 موارد مفيدة

- [Laravel Exception Handling](https://laravel.com/docs/12.x/errors)
- [Laravel Testing](https://laravel.com/docs/12.x/testing)
- [Laravel Caching](https://laravel.com/docs/12.x/cache)
- [Laravel Security](https://laravel.com/docs/12.x/security)

---

**آخر تحديث:** 15 ديسمبر 2025
