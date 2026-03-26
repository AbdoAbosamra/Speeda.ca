# 🎯 أكواد جاهزة للتنفيذ الفوري
## Reviews, Comments, و Filtering System

---

## ✅ الملفات الجديدة الكاملة

### 1️⃣ ServiceProvider Model - Scopes الجديدة

**الملف**: `app/Models/ServiceProvider.php` (إضافة في نهاية الـ Class)

```php
// قسم الـ Scopes الجديدة
public function scopeWithAverageRating($query)
{
    return $query->withCount('reviews')
        ->withAvg('reviews', 'rating');
}

public function scopeFilterByMinRating($query, $minRating = 0)
{
    if ($minRating <= 0) {
        return $query;
    }

    return $query->whereHas('reviews', function ($q) use ($minRating) {
        $q->where('is_active', true);
    }, '>=', 1)
    ->having(\DB::raw('AVG(COALESCE((SELECT AVG(rating) FROM service_provider_reviews WHERE service_provider_id = service_providers.id AND is_active = 1), 0))'), '>=', $minRating);
}

public function scopeFilterByVerifiedReviews($query)
{
    return $query->whereHas('reviews', function ($q) {
        $q->where('is_active', true)
          ->where('is_verified', true);
    });
}

public function scopeOrderByRating($query, $direction = 'desc')
{
    return $query->orderByRaw(
        '(SELECT COALESCE(AVG(rating), 0) FROM service_provider_reviews 
          WHERE service_provider_id = service_providers.id AND is_active = 1) ' . $direction
    );
}

public function scopeOrderByReviewCount($query, $direction = 'desc')
{
    return $query->withCount('reviews')
        ->orderBy('reviews_count', $direction);
}

// Accessors للـ Computed Values
public function getAverageRatingAttribute()
{
    if (!$this->relationLoaded('reviews')) {
        return $this->reviews()
            ->where('is_active', true)
            ->avg('rating') ?? 0;
    }

    $activeReviews = $this->reviews->filter(fn($r) => $r->is_active);
    return $activeReviews->count() > 0 ? $activeReviews->avg('rating') : 0;
}

public function getReviewsCountAttribute()
{
    if (!$this->relationLoaded('reviews')) {
        return $this->reviews()
            ->where('is_active', true)
            ->count();
    }

    return $this->reviews->filter(fn($r) => $r->is_active)->count();
}

public function getVerifiedReviewsCountAttribute()
{
    if (!$this->relationLoaded('reviews')) {
        return $this->reviews()
            ->where('is_active', true)
            ->where('is_verified', true)
            ->count();
    }

    return $this->reviews
        ->filter(fn($r) => $r->is_active && $r->is_verified)
        ->count();
}

// Method للتحقق من وجود review من المستخدم
public function hasUserReviewed($userId)
{
    return $this->reviews()
        ->where('client_id', $userId)
        ->exists();
}
```

---

### 2️⃣ Review Scopes والـ Relations

**الملف**: `app/Models/Review.php` - تحديثات

```php
// إضافة هذه الـ Methods:

public function scopeActive($query)
{
    return $query->where('is_active', true);
}

public function scopeVerified($query)
{
    return $query->where('is_verified', true);
}

public function scopeFeatured($query)
{
    return $query->where('is_featured', true);
}

public function scopePending($query)
{
    return $query->where('is_active', false);
}

public function scopeOrderByRating($query, $direction = 'desc')
{
    return $query->orderBy('rating', $direction);
}

public function getStarsAttribute()
{
    return str_repeat('★', $this->rating) . str_repeat('☆', 5 - $this->rating);
}
```

---

### 3️⃣ Comment Scopes

**الملف**: `app/Models/Comment.php` - تحديثات

```php
public function scopeActive($query)
{
    return $query->where('is_active', true);
}

public function scopePending($query)
{
    return $query->where('is_active', false);
}

public function scopeFlagged($query)
{
    return $query->where('is_flagged', true);
}
```

---

### 4️⃣ تحديث ServiceProviderController

**الملف**: `app/Http/Controllers/ServiceProviderController.php` - method index()

```php
public function index(Request $request)
{
    try {
        $query = ServiceProvider::query();

        // البحث
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('company_name', 'LIKE', "%{$search}%")
                    ->orWhere('bio', 'LIKE', "%{$search}%")
                    ->orWhere('services_offered', 'LIKE', "%{$search}%");
            });
        }

        // فلتر الفئة
        if ($request->filled('category')) {
            $category = $request->input('category');
            $query->where('category_id', $category);
        }

        // فلتر الموقع
        if ($request->filled('location')) {
            $query->where('location_id', $request->input('location'));
        }

        // ===== الفلاترات الجديدة =====

        // فلتر التقييمات الدنيا
        if ($request->filled('min_rating') && $request->input('min_rating') > 0) {
            $minRating = $request->input('min_rating');
            $query->whereHas('reviews', function ($q) use ($minRating) {
                $q->where('is_active', true)
                  ->groupBy('service_provider_id')
                  ->havingRaw('AVG(rating) >= ?', [$minRating]);
            });
        }

        // فلتر التقييمات المعتمدة فقط
        if ($request->boolean('verified_only')) {
            $query->whereHas('reviews', function ($q) {
                $q->where('is_active', true)
                  ->where('is_verified', true);
            });
        }

        // الـ Sorting
        $sortBy = $request->input('sort_by', 'rating');
        
        switch ($sortBy) {
            case 'rating_high':
                $query->orderByRating('desc');
                break;
            case 'rating_low':
                $query->orderByRating('asc');
                break;
            case 'reviews_count':
                $query->orderByReviewCount('desc');
                break;
            case 'newest':
                $query->latest('service_providers.created_at');
                break;
            default:
                $query->orderByRating('desc');
        }

        // Load مع Relations
        $serviceProviders = $query->with(['user', 'category', 'location', 'reviews' => function ($q) {
            $q->where('is_active', true)->latest('created_at');
        }])
        ->paginate(12)
        ->withQueryString();

        // البيانات للـ Selects
        $categories = Category::where('is_active', 1)
            ->whereNull('parent_id')
            ->orderBy('name')
            ->get();
        
        $locations = Location::where('is_active', 1)
            ->orderBy('city')
            ->get();

        $revealedContacts = session('revealed_contacts', []);

        return view('service-providers.index', compact(
            'serviceProviders',
            'categories',
            'locations',
            'revealedContacts'
        ));

    } catch (\Exception $e) {
        Log::error('Service providers index error: ' . $e->getMessage());
        return redirect()->back()->with('error', __('general.error_occurred'));
    }
}
```

---

### 5️⃣ Helper Method في User Model

**الملف**: `app/Models/User.php` - إضافة

```php
public function hasReviewedProvider($provider)
{
    if (!$this->isClient()) {
        return true; // Non-clients can't review anyway
    }

    return Review::where('client_id', $this->id)
        ->where('service_provider_profile_id', $provider->id)
        ->exists();
}
```

---

## 🎨 Blade Components الجديدة

### 6️⃣ Review Card Component

**الملف**: `resources/views/components/review-card.blade.php`

```blade
@props([
    'review',
    'showStatus' => false,
    'showActions' => false,
])

<div class="review-card bg-white rounded-lg border border-gray-200 p-6 hover:shadow-md transition-shadow">
    <!-- الرأس -->
    <div class="flex items-start justify-between mb-4">
        <div class="flex-1">
            <div class="flex items-center gap-3">
                <!-- Avatar -->
                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-purple-500 flex items-center justify-center text-white font-bold text-sm">
                    {{ strtoupper(substr($review->client->name ?? 'A', 0, 1)) }}
                </div>
                <!-- معلومات المستخدم -->
                <div>
                    <p class="font-semibold text-gray-900">
                        {{ $review->client->name ?? __('reviews.anonymous_user') }}
                    </p>
                    <p class="text-sm text-gray-500">{{ $review->created_at->diffForHumans() }}</p>
                </div>
            </div>
        </div>

        <!-- التقييم والنجوم -->
        <div class="text-right">
            <div class="flex gap-0.5 justify-end mb-1 text-lg text-yellow-400">
                @for ($i = 1; $i <= 5; $i++)
                    <span>{{ $i <= $review->rating ? '★' : '☆' }}</span>
                @endfor
            </div>
            <span class="text-2xl font-bold text-gray-900">{{ $review->rating }}</span>
            <span class="text-sm text-gray-500">/5</span>
        </div>
    </div>

    <!-- نص التقييم -->
    <p class="text-gray-700 mb-4 leading-relaxed">{{ $review->review_text }}</p>

    <!-- الـ Badges -->
    <div class="flex flex-wrap gap-2 mb-4">
        @if ($review->is_verified)
            <span class="inline-flex items-center gap-1 bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-medium">
                <i class="fas fa-check-circle"></i>
                {{ __('reviews.verified_purchase') }}
            </span>
        @endif

        @if ($review->is_featured)
            <span class="inline-flex items-center gap-1 bg-amber-100 text-amber-700 px-3 py-1 rounded-full text-xs font-medium">
                <i class="fas fa-star"></i>
                {{ __('reviews.featured_review') }}
            </span>
        @endif

        @if ($showStatus && !$review->is_active)
            <span class="inline-flex items-center gap-1 bg-yellow-100 text-yellow-700 px-3 py-1 rounded-full text-xs font-medium">
                <i class="fas fa-clock"></i>
                {{ __('reviews.pending_approval') }}
            </span>
        @endif
    </div>

    <!-- الإجراءات -->
    @if ($showActions)
        <div class="flex gap-3 pt-4 border-t border-gray-200">
            @if (Auth::check() && Auth::user()->id === $review->client_id && !$review->is_active)
                <a href="{{ route('reviews.edit', $review) }}" 
                   class="text-blue-600 hover:text-blue-800 text-sm font-medium inline-flex items-center gap-1">
                    <i class="fas fa-edit"></i>
                    {{ __('general.edit') }}
                </a>
            @endif

            @if (Auth::check() && (Auth::user()->id === $review->client_id || Auth::user()->isAdmin()))
                <form method="POST" action="{{ route('reviews.destroy', $review) }}" class="inline"
                      onsubmit="return confirm('{{ __('reviews.confirm_delete') }}?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-red-600 hover:text-red-800 text-sm font-medium inline-flex items-center gap-1">
                        <i class="fas fa-trash"></i>
                        {{ __('general.delete') }}
                    </button>
                </form>
            @endif
        </div>
    @endif
</div>
```

---

### 7️⃣ Comment Card Component

**الملف**: `resources/views/components/comment-card.blade.php`

```blade
@props([
    'comment',
    'showStatus' => false,
    'showActions' => false,
])

<div class="comment-card bg-gray-50 rounded-lg border border-gray-200 p-4">
    <div class="flex gap-4">
        <!-- Avatar -->
        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-green-500 to-blue-500 flex-shrink-0 flex items-center justify-center text-white font-bold text-sm">
            {{ strtoupper(substr($comment->user->name ?? 'A', 0, 1)) }}
        </div>

        <!-- المحتوى -->
        <div class="flex-1">
            <!-- الرأس -->
            <div class="flex items-center justify-between mb-2">
                <div>
                    <p class="font-semibold text-gray-900 text-sm">{{ $comment->user->name ?? __('general.user') }}</p>
                    <p class="text-xs text-gray-500">{{ $comment->created_at->diffForHumans() }}</p>
                </div>

                @if ($showStatus && !$comment->is_active)
                    <span class="inline-flex items-center gap-1 bg-yellow-100 text-yellow-700 px-2 py-1 rounded text-xs font-medium">
                        <i class="fas fa-clock"></i>
                        {{ __('comments.pending') }}
                    </span>
                @endif
            </div>

            <!-- النص -->
            <p class="text-gray-700 text-sm mb-3">{{ $comment->content }}</p>

            <!-- الإجراءات -->
            @if ($showActions)
                <div class="flex gap-3 text-xs font-medium">
                    @if (Auth::check() && Auth::user()->id === $comment->user_id && !$comment->is_active)
                        <a href="{{ route('comments.edit', $comment) }}" 
                           class="text-blue-600 hover:text-blue-800">
                            <i class="fas fa-edit mr-1"></i>{{ __('general.edit') }}
                        </a>
                    @endif

                    @if (Auth::check() && (Auth::user()->id === $comment->user_id || Auth::user()->isAdmin()))
                        <form method="POST" action="{{ route('comments.destroy', $comment) }}" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-800"
                                    onclick="return confirm('{{ __('comments.confirm_delete') }}?');">
                                <i class="fas fa-trash mr-1"></i>{{ __('general.delete') }}
                            </button>
                        </form>
                    @endif

                    @if (Auth::check() && Auth::user()->id !== $comment->user_id)
                        <form method="POST" action="{{ route('comments.flag', $comment) }}" class="inline">
                            @csrf
                            <button type="submit" class="text-amber-600 hover:text-amber-800">
                                <i class="fas fa-flag mr-1"></i>{{ __('comments.flag') }}
                            </button>
                        </form>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>
```

---

## 📝 الترجمات المطلوبة

### 8️⃣ lang/ar/reviews.php - الإضافات

```php
// إضافة في الملف الموجود:
'average_rating' => 'متوسط التقييم',
'total_reviews' => 'إجمالي التقييمات',
'rating_distribution' => 'توزيع التقييمات',
'no_reviews_yet' => 'لا توجد تقييمات بعد',
'be_first_to_review' => 'كن أول من يقيم هذه الخدمة',
'write_review' => 'اكتب تقييماً',
'login_to_review' => 'قم بتسجيل الدخول لكتابة تقييم',
'poor' => 'ضعيف',
'fair' => 'مقبول',
'good' => 'جيد',
'very_good' => 'جيد جداً',
'excellent' => 'ممتاز',
'review_for' => 'تقييم لـ',
'describe_experience' => 'صف تجربتك مع هذه الخدمة...',
'submit_review' => 'إرسال التقييم',
'review_note' => 'سيتم نشر تقييمك بعد موافقة الإدارة',
'view_reviews_for' => 'التقييمات لـ',
'showing_active_reviews' => 'عرض التقييمات المعتمدة',
'verified_purchase' => 'شراء معتمد',
'featured_review' => 'تقييم مميز',
'highest_rated' => 'الأعلى تقييماً',
'most_reviewed' => 'الأكثر تقييماً',
'minimum_rating' => 'أقل تقييم',
'verified_reviews_only' => 'التقييمات المعتمدة فقط',
'confirm_delete' => 'هل تأكد أنك تريد حذف هذا التقييم؟',
```

### 9️⃣ lang/ar/comments.php - الإضافات

```php
'pending' => 'قيد الانتظار',
'comment_submitted_pending_approval' => 'تم إرسال تعليقك سينشر بعد الموافقة',
'discussion' => 'النقاش والتعليقات',
'write_comment' => 'اكتب تعليقك...',
'post_comment' => 'نشر التعليق',
'confirm_delete' => 'هل تأكد أنك تريد حذف هذا التعليق؟',
'flag' => 'إبلاغ',
'only_clients_can_comment' => 'فقط العملاء يمكنهم التعليق',
```

---

## 🔧 خطوات التطبيق السريعة

### الخطوة 1: إضافة الـ Scopes في Models (15 دقيقة)
```bash
# في ServiceProvider.php أضف الـ methods من الأعلى
# في Review.php أضف الـ scopes
# في Comment.php أضف الـ scopes
```

### الخطوة 2: إنشاء Components (20 دقيقة)
```bash
# الملف: resources/views/components/review-card.blade.php
# الملف: resources/views/components/comment-card.blade.php
```

### الخطوة 3: تحديث Controller (20 دقيقة)
```bash
# في ServiceProviderController.php - استبدل method index()
```

### الخطوة 4: إضافة الترجمات (15 دقيقة)
```bash
# lang/ar/reviews.php
# lang/en/reviews.php (same keys in English)
```

### الخطوة 5: تحديث الـ Views (30 دقيقة)
```bash
# استخدم الـ components الجديدة في الـ views القديمة
```

---

**الحالة**: كل الأكواد جاهزة للنسخ واللصق! ✅
