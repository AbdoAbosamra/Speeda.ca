# Session-Based Contact Reveal System

## المشكلة السابقة
كان النظام يستخدم `localStorage` لحفظ قائمة الـ service providers اللي المستخدم ضغط على "Contact via WhatsApp" عندهم. المشكلة إن localStorage مشترك بين كل المستخدمين على نفس الجهاز/متصفح، فلو حد ضغط على زر التواصل، المعلومات (الرقم والعنوان) كانت بتظهر لأي حد تاني يفتح نفس الصفحة على نفس الجهاز.

## الحل
تم تغيير النظام لاستخدام **PHP Sessions** بدلاً من localStorage. الـ Session مرتبط بكل مستخدم بشكل منفصل، سواء كان مسجل دخول أو زائر (guest).

## التغييرات

### 1. Route جديد (web.php)
```php
// Track contact reveal (no auth required, uses session)
Route::post('/service-providers/{serviceProvider}/reveal-contact', [ServiceProviderController::class, 'revealContact'])
    ->name('service-providers.reveal-contact');
```

### 2. Controller Method جديد (ServiceProviderController.php)
```php
/**
 * Track when a user reveals contact information
 * Uses session to ensure privacy - only the user who clicked sees the info
 */
public function revealContact(Request $request, ServiceProvider $serviceProvider)
{
    // Get existing revealed contacts from session
    $revealedContacts = session('revealed_contacts', []);
    
    // Add this provider if not already revealed
    if (!in_array($serviceProvider->id, $revealedContacts)) {
        $revealedContacts[] = $serviceProvider->id;
        session(['revealed_contacts' => $revealedContacts]);
    }
    
    return response()->json([
        'success' => true,
        'message' => 'Contact revealed'
    ]);
}
```

### 3. تعديل show() method
```php
// Check if this user has revealed this provider's contact
$isContactRevealed = session()->has('revealed_contacts') && 
                     in_array($serviceProvider->id, session('revealed_contacts', []));

return view('service-providers.show', compact(
    'serviceProvider',
    'locations',
    'similarProviders',
    'formattedNumber',
    'isContactRevealed'  // <-- متغير جديد
));
```

### 4. تعديل index() method
```php
// Get list of revealed contacts from session
$revealedContacts = session('revealed_contacts', []);

return view('service-providers.index', compact(
    'serviceProviders', 
    'categories', 
    'locations', 
    'revealedContacts'  // <-- متغير جديد
));
```

### 5. تعديل JavaScript في show.blade.php
**قبل:**
```javascript
// Save provider ID to localStorage to reveal address in listing page
const providerId = {{ $serviceProvider->id }};
const revealedAddresses = JSON.parse(localStorage.getItem('revealedAddresses') || '[]');
if (!revealedAddresses.includes(providerId)) {
    revealedAddresses.push(providerId);
    localStorage.setItem('revealedAddresses', JSON.stringify(revealedAddresses));
}
```

**بعد:**
```javascript
// Store reveal in SESSION (server-side) instead of localStorage
const providerId = {{ $serviceProvider->id }};
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

// Store reveal in session via AJAX request
if (csrfToken) {
    fetch(`/service-providers/${providerId}/reveal-contact`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({})
    }).catch(err => console.error('Failed to store reveal:', err));
}
```

### 6. تعديل عرض البيانات في show.blade.php
**Phone Number:**
```php
@php
    $phoneDisplay = $serviceProvider->phone;
    if($isContactRevealed) {
        // Show full number if already revealed
        $displayPhone = $phoneDisplay;
        $phoneClass = 'text-success fw-bold';
    } else {
        // Hide last 3 digits if not revealed
        if(strlen($phoneDisplay) > 3) {
            $displayPhone = substr($phoneDisplay, 0, -3) . '***';
        } else {
            $displayPhone = '***';
        }
        $phoneClass = 'text-muted';
    }
@endphp
<span id="phoneNumber" class="{{ $phoneClass }}">{{ $displayPhone }}</span>
@if(!$isContactRevealed)
<small class="d-block text-muted"><i class="fas fa-lock me-1"></i>{{ __('service_provider.phone_reveal_hint') }}</small>
@endif
```

**نفس المنطق تم تطبيقه على:**
- WhatsApp Number
- Address

### 7. تعديل index.blade.php
**قبل:**
```javascript
const revealedAddresses = JSON.parse(localStorage.getItem('revealedAddresses') || '[]');
```

**بعد:**
```javascript
const revealedContacts = @json($revealedContacts ?? []);
```

## الفوائد

### 1. **الخصوصية الكاملة**
- كل مستخدم يشوف بس المعلومات اللي هو ضغط عليها
- لو شخصين فتحوا نفس الصفحة على نفس الجهاز، كل واحد يشوف بياناته الخاصة

### 2. **Server-Side Control**
- التحكم بالـ reveal على السيرفر مش على المتصفح
- صعب التلاعب بالبيانات من المتصفح

### 3. **Multi-Device Support**
- لو المستخدم عنده حساب، الـ session بتتزامن معاه على كل أجهزته (لو استخدمنا session driver مناسب)

### 4. **Security**
- CSRF protection موجود
- البيانات محفوظة على السيرفر مش في المتصفح

## سلوك الـ Session

### Guest Users (زوار)
- PHP بيعمل session تلقائي لكل زائر
- Session ID بيتحفظ في cookie اسمه `laravel_session`
- كل زائر بيكون عنده session منفصل تماماً

### Logged-In Users
- نفس الآلية بس الـ session مربوط بالـ user ID
- لو المستخدم عمل logout وlogin تاني، الـ session بتتجدد

## Session Lifetime
حسب الإعدادات في `config/session.php`:
```php
'lifetime' => env('SESSION_LIFETIME', 120), // 120 دقيقة (ساعتين)
```

بعد انتهاء الـ session، المستخدم هيحتاج يدوس على زر التواصل تاني عشان يشوف المعلومات.

## اختبار النظام

### Test 1: Same Browser, Different Tabs
1. افتح صفحة service provider في tab
2. اضغط "Contact via WhatsApp"
3. لاحظ إن الرقم والعنوان ظهروا
4. افتح نفس الصفحة في tab تاني
5. **النتيجة:** الرقم والعنوان لسه ظاهرين (نفس الـ session)

### Test 2: Different Browsers
1. افتح صفحة service provider في Chrome
2. اضغط "Contact via WhatsApp"
3. افتح نفس الصفحة في Firefox
4. **النتيجة:** الرقم والعنوان مخفيين (session مختلف)

### Test 3: Incognito Mode
1. افتح صفحة service provider في normal mode
2. اضغط "Contact via WhatsApp"
3. افتح نفس الصفحة في incognito mode
4. **النتيجة:** الرقم والعنوان مخفيين (session مختلف)

### Test 4: Different Users on Same Device
1. User A يفتح الصفحة ويدوس Contact
2. User B يفتح نفس الصفحة على نفس الجهاز
3. **النتيجة:** User B مش هيشوف المعلومات (session مختلف)

## الملفات المعدلة
1. `routes/web.php` - Route جديد
2. `app/Http/Controllers/ServiceProviderController.php` - Method جديد + تعديل show/index
3. `resources/views/service-providers/show.blade.php` - PHP logic + JavaScript
4. `resources/views/service-providers/index.blade.php` - JavaScript

## ملاحظات مهمة
- الـ CSRF token موجود في الصفحة (`<meta name="csrf-token">`)
- الـ fetch request بيستخدم POST method مع CSRF protection
- الـ session lifetime قابل للتغيير من `.env` عن طريق `SESSION_LIFETIME`
- لو حد محا الـ cookies، الـ session هتضيع والمعلومات هترجع تتخفي

## Future Enhancements (اختياري)
1. **Database-based session** بدلاً من file-based للأداء الأفضل
2. **Analytics**: تسجيل كام مرة user ضغط على contact button
3. **Rate Limiting**: منع spam على الـ reveal endpoint
4. **Expiry per provider**: كل provider يكون ليه expiry منفصل في الـ session
