# الميزات المقترحة والتحسينات - Speeda

**التاريخ:** 15 ديسمبر 2025  
**الإصدار:** v1.1 - Roadmap

---

## 🎯 رؤية المشروع

تطبيق Speeda يهدف إلى ربط العملاء برجال الأعمال والخدمات المتخصصة. المرحلة الحالية تركز على الأساسيات، والمرحلة القادمة يجب أن تركز على التجربة الشاملة.

---

## 🚀 الميزات الجديدة المقترحة

### المجموعة 1: نظام الحجوزات المتكامل

#### ✨ الميزة 1.1: إنشاء وإدارة الحجوزات

**الوصف:**
- عملاء يحجزون الخدمات من مزودي الخدمات
- جدول زمني واضح
- تأكيد تلقائي أو يدوي

**المسارات المطلوبة:**
```php
POST     /bookings                    // إنشاء حجز
GET      /bookings                    // عرض حجوزاتي
GET      /bookings/{id}               // تفاصيل الحجز
PATCH    /bookings/{id}               // تحديث الحجز
DELETE   /bookings/{id}               // إلغاء الحجز
POST     /bookings/{id}/confirm       // تأكيد الحجز
POST     /bookings/{id}/complete      // إكمال الحجز
POST     /bookings/{id}/cancel        // إلغاء الحجز
```

**Statuses:**
```
pending       → في الانتظار
confirmed     → مؤكد
in_progress   → جاري التنفيذ
completed     → مكتمل
cancelled     → ملغى
```

**الجدول:**
```php
Schema::create('bookings', function (Blueprint $table) {
    $table->id();
    
    // العلاقات
    $table->foreignId('service_provider_id');
    $table->foreignId('client_id');
    
    // المعلومات الأساسية
    $table->string('booking_reference')->unique();
    $table->enum('status', [...]);
    $table->text('service_description');
    $table->text('client_requirements')->nullable();
    
    // التواريخ والأوقات
    $table->dateTime('preferred_date');
    $table->dateTime('confirmed_date')->nullable();
    $table->dateTime('completed_date')->nullable();
    
    // التكاليف
    $table->decimal('estimated_cost', 10, 2)->nullable();
    $table->decimal('final_cost', 10, 2)->nullable();
    $table->enum('currency', ['SAR', 'USD', 'AED'])->default('SAR');
    
    // الدفع
    $table->enum('payment_status', ['pending', 'completed', 'refunded']);
    $table->string('payment_method')->nullable();
    
    // الموقع والتواصل
    $table->text('service_address');
    $table->string('client_phone');
    $table->text('special_instructions')->nullable();
    
    $table->timestamps();
    $table->softDeletes();
});
```

**المدة:** 3-4 ساعات

---

#### ✨ الميزة 1.2: إدارة جدول العمل (Calendar)

**الوصف:**
- تصور بياني لجدول الحجوزات
- إظهار التوفر والاشتغال
- حجز سريع من التقويم

**المشروطة:**
- إضافة مكتبة JavaScript للتقويم
- تقويم متجاوب responsive

**المسارات:**
```php
GET      /service-providers/{id}/calendar
GET      /service-providers/{id}/availability/{date}
```

**المدة:** 2-3 ساعات

---

### المجموعة 2: نظام التقييمات والتعليقات

#### ✨ الميزة 2.1: نظام التقييم

**الوصف:**
- عملاء يقيمون مزودي الخدمات
- تقييمات من 1-5 نجوم
- تعليقات مفصلة

**الجدول:**
```php
Schema::create('reviews', function (Blueprint $table) {
    $table->id();
    
    // العلاقات
    $table->foreignId('service_provider_id');
    $table->foreignId('client_id');
    $table->foreignId('booking_id')->nullable();
    
    // التقييم
    $table->unsignedTinyInteger('rating'); // 1-5
    $table->longText('comment')->nullable();
    $table->boolean('verified_purchase')->default(false);
    
    // الفئات
    $table->unsignedTinyInteger('quality_rating')->nullable(); // 1-5
    $table->unsignedTinyInteger('communication_rating')->nullable(); // 1-5
    $table->unsignedTinyInteger('punctuality_rating')->nullable(); // 1-5
    $table->unsignedTinyInteger('professionalism_rating')->nullable(); // 1-5
    
    // الصور
    $table->json('images')->nullable();
    
    // الإدارة
    $table->boolean('is_helpful')->default(false);
    $table->integer('helpful_count')->default(0);
    $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
    
    $table->timestamps();
    $table->softDeletes();
});
```

**المسارات:**
```php
POST     /service-providers/{id}/reviews
GET      /service-providers/{id}/reviews
PATCH    /reviews/{id}
DELETE   /reviews/{id}
```

**المدة:** 2-3 ساعات

---

#### ✨ الميزة 2.2: تقرير مفصل للمزود

**الوصف:**
- لوحة تحكم للمزود
- إحصائيات التقييمات
- التقارير الشهرية

**المسارات:**
```php
GET      /service-providers/dashboard
GET      /service-providers/dashboard/stats
GET      /service-providers/dashboard/earnings
```

**المدة:** 3-4 ساعات

---

### المجموعة 3: نظام الإشعارات

#### ✨ الميزة 3.1: إشعارات البريد الإلكتروني

**الوصف:**
- إشعار بالحجز الجديد
- تذكيرات قبل الموعد
- إشعار بالتقييم الجديد

**الإشعارات:**
```
1. Booking Notification
   - To: Service Provider
   - When: Booking created
   - Content: New booking details

2. Booking Confirmation
   - To: Client
   - When: Booking confirmed
   - Content: Confirmed booking details

3. Booking Reminder
   - To: Both
   - When: 24 hours before
   - Content: Reminder about upcoming booking

4. Review Notification
   - To: Service Provider
   - When: New review
   - Content: New review details

5. Payment Confirmation
   - To: Both
   - When: Payment completed
   - Content: Receipt and details
```

**الملفات المطلوبة:**
```php
// app/Notifications/BookingCreated.php
class BookingCreated extends Notification
{
    use Queueable;
    
    public function via($notifiable)
    {
        return ['mail'];
    }
    
    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->subject('Booking Confirmation')
                    ->line('You have a new booking')
                    ->action('View Booking', url('/bookings/' . $this->booking->id));
    }
}
```

**المدة:** 2 ساعة

---

#### ✨ الميزة 3.2: إشعارات داخل التطبيق (In-App)

**الوصف:**
- جرس الإشعارات
- بتعليقات العدد
- قائمة الإشعارات

**المسارات:**
```php
GET      /notifications
GET      /notifications/unread
POST     /notifications/{id}/read
DELETE   /notifications/{id}
```

**المدة:** 2 ساعة

---

#### ✨ الميزة 3.3: إشعارات SMS و WhatsApp

**الوصف:**
- إرسال رسائل نصية قصيرة
- رسائل WhatsApp تلقائية

**مثال التكامل:**
```php
// app/Services/SmsService.php
class SmsService
{
    public function sendBookingNotification(Booking $booking)
    {
        $message = "Your booking with {$booking->serviceProvider->company_name} "
                 . "is confirmed for {$booking->confirmed_date}";
        
        // استخدام Twilio أو API سعودي
        $this->client->messages->create($booking->client->phone, [
            'from' => config('sms.from'),
            'body' => $message
        ]);
    }
}
```

**المدة:** 3 ساعات

---

### المجموعة 4: نظام الدفع

#### ✨ الميزة 4.1: معالجة الدفع

**الوصف:**
- تكامل مع بوابات الدفع
- عملة محلية
- فواتير تلقائية

**البوابات المدعومة:**
```
- Apple Pay
- Google Pay
- Stripe
- 2Checkout
- PayTabs (محلية)
- Fawry (مصري)
```

**الملفات:**
```php
// app/Services/PaymentService.php
interface PaymentProvider
{
    public function processPayment(Booking $booking): PaymentResult;
    public function refund(Booking $booking): RefundResult;
    public function getBalance(): decimal;
}

class StripePaymentProvider implements PaymentProvider
{
    // ...
}

class ApplePayProvider implements PaymentProvider
{
    // ...
}
```

**المدة:** 4-5 ساعات

---

#### ✨ الميزة 4.2: الفواتير والإيصالات

**الوصف:**
- إنشاء فواتير تلقائية
- تحميل PDF
- سجل الفواتير

**المسارات:**
```php
GET      /bookings/{id}/invoice
GET      /bookings/{id}/invoice/pdf
GET      /invoices
```

**المدة:** 2 ساعة

---

### المجموعة 5: نظام البحث والفلترة المتقدم

#### ✨ الميزة 5.1: بحث متقدم

**الوصف:**
- بحث بالاسم والفئة
- فلترة بالسعر والتقييم
- ترتيب متقدم

**المسارات:**
```php
GET      /service-providers/search
    ?q=search
    &category=5
    &location=3
    &min_rating=4
    &min_price=100
    &max_price=1000
    &sort=rating|price|views
    &order=asc|desc
```

**المدة:** 2-3 ساعات

---

#### ✨ الميزة 5.2: البحث الجغرافي

**الوصف:**
- البحث حسب الموقع الحالي
- عرض المزودين بالقرب

**المتطلبات:**
- Google Maps API
- Geolocation API

**المدة:** 2-3 ساعات

---

### المجموعة 6: المحفوظات والملفات الشخصية

#### ✨ الميزة 6.1: حفظ المزودين (Wishlist)

**الوصف:**
- حفظ المزودين المفضلين
- إدارة قوائم التوفير

**الجدول:**
```php
Schema::create('saved_providers', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id');
    $table->foreignId('service_provider_id');
    $table->string('note')->nullable();
    $table->timestamps();
    
    $table->unique(['user_id', 'service_provider_id']);
});
```

**المسارات:**
```php
POST     /service-providers/{id}/save
DELETE   /service-providers/{id}/unsave
GET      /saved-providers
```

**المدة:** 1 ساعة

---

#### ✨ الميزة 6.2: ملف شخصي محسّن

**الوصف:**
- صورة ملف شخصي
- سيرة ذاتية
- شهادات

**المسارات:**
```php
GET      /profile
PATCH    /profile
POST     /profile/avatar
DELETE   /profile/avatar
```

**المدة:** 1 ساعة

---

### المجموعة 7: تحليلات وتقارير

#### ✨ الميزة 7.1: لوحة التحكم (Dashboard)

**الوصف:**
- للعملاء: سجل الحجوزات
- للمزودين: إحصائيات الأداء
- للمسؤولين: إحصائيات عامة

**الرسوم البيانية:**
```
- عدد الحجوزات الشهري
- الإيرادات الشهرية
- التقييمات المتوسطة
- عدد المشاهدات
- معدل التحويل
```

**المسارات:**
```php
GET      /dashboard
GET      /dashboard/stats
GET      /dashboard/charts/{type}
```

**المدة:** 3-4 ساعات

---

#### ✨ الميزة 7.2: تقارير Excel/CSV

**الوصف:**
- تصدير البيانات
- تقارير مخصصة
- جدولة التقارير

**المسارات:**
```php
GET      /reports/bookings/export
GET      /reports/earnings/export
GET      /reports/reviews/export
```

**المدة:** 2 ساعة

---

## 🔧 التحسينات التقنية

### 1. API RESTful كامل

**الوصف:**
- نقاط نهاية API شاملة
- Versioning (v1, v2, ...)
- Documentation مع Swagger

**المسارات:**
```
GET      /api/v1/service-providers
POST     /api/v1/service-providers
GET      /api/v1/service-providers/{id}
PATCH    /api/v1/service-providers/{id}
DELETE   /api/v1/service-providers/{id}

// نفس الشيء للفئات والمواقع والحجوزات
```

**المدة:** 3-4 ساعات

---

### 2. تحسينات الأداء

**الخطوات:**
- Redis Caching
- Database Query Optimization
- CDN للصور والملفات
- Lazy Loading
- Pagination محسّن

**المدة:** 4-5 ساعات

---

### 3. نظام الترجمة الشامل

**اللغات:**
- العربية ✓
- الإنجليزية ✓
- الفرنسية ✓
- إضافة: الإسبانية، الألمانية، الهندية

**المسارات:**
```php
GET      /lang/{locale}
POST     /user/language
```

**المدة:** 2 ساعة

---

### 4. الأمان المحسّن

**الميزات:**
- Two-Factor Authentication (2FA)
- OAuth2 Integration
- IP Whitelisting
- CORS Security
- DDoS Protection

**المدة:** 4-5 ساعات

---

### 5. الاستجابة للأجهزة المختلفة

**الأنظمة:**
- تطبيق ويب (Web) ✓
- تطبيق iOS (React Native)
- تطبيق Android (React Native)
- تطبيق PWA (Progressive Web App)

**المدة:** 10-15 ساعة

---

## 📊 خريطة الطريق (Roadmap)

### Q1 2025 (الربع الأول):
```
✓ إصلاح الأخطاء الحرجة
✓ تحسينات البنية
✓ نظام الحجوزات الأساسي
✓ نظام التقييمات
```

### Q2 2025 (الربع الثاني):
```
◻ نظام الدفع
◻ نظام الإشعارات المتقدم
◻ البحث المتقدم
◻ API كامل
```

### Q3 2025 (الربع الثالث):
```
◻ تطبيق موبايل
◻ تحسينات الأداء
◻ لوحة التحكم
◻ تقارير محسّنة
```

### Q4 2025 (الربع الرابع):
```
◻ أمان محسّن
◻ ترجمات إضافية
◻ PWA
◻ إطلاق العام
```

---

## 💰 تقدير الجهد والموارد

| الميزة | الجهد (ساعة) | الأولوية | الربع |
|--------|-------------|---------|------|
| إصلاحات الأخطاء | 5 | 🔴 عالي | Q1 |
| نظام الحجوزات | 8 | 🔴 عالي | Q1 |
| نظام التقييمات | 6 | 🟡 متوسط | Q1 |
| نظام الإشعارات | 6 | 🟡 متوسط | Q2 |
| نظام الدفع | 10 | 🔴 عالي | Q2 |
| البحث المتقدم | 5 | 🟡 متوسط | Q2 |
| تطبيق الموبايل | 40 | 🟡 متوسط | Q3 |
| التحليلات | 8 | 🟢 منخفض | Q3 |
| الأمان المحسّن | 10 | 🔴 عالي | Q4 |

**الإجمالي:** ~100 ساعة (حوالي 3 أسابيع بفريق مطورين)

---

## 📱 مثال: عملية حجز كاملة

```
1. العميل يبحث عن خدمة
   GET /service-providers?category=5&location=3

2. العميل يرى تفاصيل المزود
   GET /service-providers/{id}

3. العميل ينقر على "حجز الآن"
   POST /bookings
   {
       "service_provider_id": 1,
       "preferred_date": "2025-12-20",
       "service_description": "...",
       "client_phone": "966..."
   }

4. الإشعار يُرسل للمزود
   NOTIFICATION: Booking.Created -> ServiceProvider

5. المزود يؤكد الحجز
   PATCH /bookings/{id}
   { "status": "confirmed", "confirmed_date": "2025-12-20" }

6. الإشعار يُرسل للعميل
   NOTIFICATION: Booking.Confirmed -> Client

7. تذكير قبل الموعد بـ 24 ساعة
   SCHEDULED: sendReminder()

8. إكمال الحجز
   POST /bookings/{id}/complete

9. العميل يُضيف تقييم
   POST /service-providers/{id}/reviews
   { "rating": 5, "comment": "ممتاز" }

10. المزود يرى التقييم الجديد
    NOTIFICATION: Review.Created -> ServiceProvider
```

---

## ✅ الخلاصة

هذا الخريطة الطريقية توفر رؤية واضحة لتطور Speeda من التطبيق الحالي إلى منصة شاملة وقوية. التركيز الأول على الأساسيات والأداء، ثم التوسع مع المزيد من الميزات والتكاملات.

**آخر تحديث:** 15 ديسمبر 2025
