# 🚀 خطة تطوير Reviews و Comments كـ Frontend كامل
## مع تحسين الفلاترات بناءً على Reviews و Ratings

**التاريخ**: 29 يناير 2026  
**الأولوية**: 🔴 High  
**المدة المتوقعة**: 12-16 ساعات  
**الفريق**: 2-3 مطورين (Backend + Frontend)

---

## 📋 ملخص المتطلبات

### 1️⃣ معالجة مشاكل API
```
✓ المشروع بدون API أصلاً - architecture Blade-first
✓ لا توجد endpoints مشكلة - كل شيء معمول View-based
✓ لا تحتاج معالجة
```

### 2️⃣ تطوير Reviews و Comments كـ Frontend
```
المشكلة الحالية:
- Reviews موجودة لكن UI بسيطة وغير احترافية
- Comments موجودة لكن بدون display واضح
- Approval flow موجود بدون visual indicators

المطلوب:
- UI احترافية وجميلة للـ Reviews
- UI احترافية للـ Comments
- Status indicators واضحة (Pending/Approved/etc)
- Integration مع Rating system
```

### 3️⃣ تحسين الفلاترات
```
المطلوب:
- فلتر بناءً على التقييمات (Stars)
- فلتر بناءً على عدد التقييمات
- فلتر بناءً على Verified Reviews
- Sorting بناءً على Rating/Reviews Count
```

---

## 📊 الحالة الحالية

### Reviews Controller ✓
- `index()` - عرض التقييمات (Public - Active فقط)
- `create()` - نموذج إضافة تقييم (Clients فقط)
- `store()` - حفظ التقييم (Pending approval)
- `edit()` - تعديل تقييم pending
- `update()` - تحديث التقييم
- `destroy()` - حذف التقييم
- `show()` - عرض تقييم واحد

### Comments Controller ✓
- `index()` - عرض التعليقات (Active فقط)
- `create()` - نموذج إضافة تعليق
- `store()` - حفظ التعليق (Pending)
- `edit()` - تعديل تعليق pending
- `update()` - تحديث التعليق
- `destroy()` - حذف التعليق
- `flag()` - رفع علم على تعليق

### Authorization ✓
- فقط Clients يمكنهم إضافة Reviews/Comments
- فقط صاحب التقييم يمكنه تعديله قبل الموافقة
- Admin يمكنه الموافقة/الرفض
- Soft Delete للتعليقات المحذوفة

---

## 🎯 الخطة التفصيلية

### Phase 1: تحسين UI/UX للـ Reviews (4-6 ساعات)

#### Step 1.1: إنشاء Review Card Component

**الملف**: `resources/views/components/review-card.blade.php`

```blade
@props([
    'review',
    'showStatus' => false,
    'showActions' => false,
])

<div class="review-card bg-white rounded-lg border border-gray-200 p-6 hover:shadow-lg transition-all">
    <!-- رأس التقييم -->
    <div class="flex items-start justify-between mb-4">
        <div class="flex-1">
            <!-- اسم المستخدم والوقت -->
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-purple-500 flex items-center justify-center text-white font-bold">
                    {{ strtoupper(substr($review->client->name, 0, 1)) }}
                </div>
                <div>
                    <p class="font-semibold text-gray-900">{{ $review->client->name }}</p>
                    <p class="text-sm text-gray-500">{{ $review->created_at->diffForHumans() }}</p>
                </div>
            </div>
        </div>

        <!-- النجوم والتقييم -->
        <div class="text-right">
            <div class="flex gap-1 justify-end mb-2">
                @for ($i = 1; $i <= 5; $i++)
                    <span class="text-lg {{ $i <= $review->rating ? 'text-yellow-400' : 'text-gray-300' }}">
                        ★
                    </span>
                @endfor
            </div>
            <span class="text-2xl font-bold text-gray-900">{{ number_format($review->rating, 1) }}</span>
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
                   class="text-blue-600 hover:text-blue-800 text-sm font-medium flex items-center gap-1">
                    <i class="fas fa-edit"></i>
                    {{ __('general.edit') }}
                </a>
            @endif

            @if (Auth::check() && (Auth::user()->id === $review->client_id || Auth::user()->isAdmin()))
                <form method="POST" action="{{ route('reviews.destroy', $review) }}" 
                      class="inline" onsubmit="return confirm('{{ __('reviews.confirm_delete') }}?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-red-600 hover:text-red-800 text-sm font-medium flex items-center gap-1">
                        <i class="fas fa-trash"></i>
                        {{ __('general.delete') }}
                    </button>
                </form>
            @endif
        </div>
    @endif
</div>
```

#### Step 1.2: تحسين Reviews Index View

**الملف**: `resources/views/reviews/index.blade.php` (كامل)

```blade
@extends('layouts.app')

@section('title', __('reviews.view_reviews'))

@section('content')
<div class="container mx-auto px-4 py-8 max-w-4xl">
    <!-- رأس الصفحة -->
    <div class="mb-8">
        <h1 class="text-4xl font-bold text-gray-900 mb-2">
            {{ __('reviews.view_reviews_for') }} {{ $provider->user->name }}
        </h1>
        <p class="text-gray-600">
            {{ __('reviews.showing_active_reviews') }} ({{ $reviews->total() }} {{ __('general.total') }})
        </p>
    </div>

    <!-- إحصائيات التقييمات -->
    <div class="bg-gradient-to-r from-blue-50 to-purple-50 rounded-lg p-6 mb-8 border border-blue-100">
        <div class="grid grid-cols-3 gap-4">
            <div class="text-center">
                <p class="text-sm text-gray-600 mb-1">{{ __('reviews.average_rating') }}</p>
                <div class="flex items-baseline justify-center gap-1">
                    <span class="text-4xl font-bold text-gray-900">
                        {{ number_format($provider->average_rating ?? 0, 1) }}
                    </span>
                    <span class="text-gray-500">/5</span>
                </div>
                <div class="text-lg text-yellow-400 mt-1">
                    @for ($i = 1; $i <= 5; $i++)
                        <span>{{ $i <= round($provider->average_rating ?? 0) ? '★' : '☆' }}</span>
                    @endfor
                </div>
            </div>

            <div class="text-center">
                <p class="text-sm text-gray-600 mb-1">{{ __('reviews.total_reviews') }}</p>
                <p class="text-4xl font-bold text-gray-900">{{ $reviews->total() }}</p>
                <p class="text-sm text-gray-500 mt-1">{{ __('reviews.verified') }}</p>
            </div>

            <div class="text-center">
                <p class="text-sm text-gray-600 mb-1">{{ __('reviews.rating_distribution') }}</p>
                <div class="space-y-1 text-xs">
                    @for ($rating = 5; $rating >= 1; $rating--)
                        <div class="flex items-center justify-center gap-2">
                            <span class="w-6">{{ $rating }}★</span>
                            <div class="w-20 h-2 bg-gray-200 rounded-full overflow-hidden">
                                <div class="h-full bg-yellow-400" 
                                     style="width: {{ ($provider->reviews_by_rating[$rating] ?? 0) / max($reviews->total(), 1) * 100 }}%"></div>
                            </div>
                        </div>
                    @endfor
                </div>
            </div>
        </div>
    </div>

    <!-- Reviews List -->
    @if ($reviews->count() > 0)
        <div class="space-y-4 mb-8">
            @foreach ($reviews as $review)
                <x-review-card :review="$review" :showStatus="Auth::check()" :showActions="Auth::check()" />
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-8">
            {{ $reviews->links() }}
        </div>
    @else
        <div class="text-center py-12 bg-gray-50 rounded-lg">
            <i class="fas fa-star text-4xl text-gray-300 mb-4"></i>
            <p class="text-gray-600 text-lg">{{ __('reviews.no_reviews_yet') }}</p>
            <p class="text-gray-500 text-sm">{{ __('reviews.be_first_to_review') }}</p>
        </div>
    @endif

    <!-- Call to Action -->
    @auth
        @if (Auth::user()->isClient() && !Auth::user()->hasReviewedProvider($provider))
            <div class="mt-8 text-center">
                <a href="{{ route('reviews.create', $provider) }}" 
                   class="inline-block bg-gradient-to-r from-blue-600 to-purple-600 text-white px-8 py-3 rounded-lg font-semibold hover:shadow-lg transition-all">
                    <i class="fas fa-star mr-2"></i>
                    {{ __('reviews.write_review') }}
                </a>
            </div>
        @endif
    @else
        <div class="mt-8 text-center bg-blue-50 rounded-lg p-6 border border-blue-200">
            <p class="text-gray-700 mb-3">{{ __('reviews.login_to_review') }}</p>
            <a href="{{ route('register') }}" class="inline-block bg-blue-600 text-white px-6 py-2 rounded-lg font-semibold hover:bg-blue-700 transition-colors">
                {{ __('auth.register') }}
            </a>
        </div>
    @endauth
</div>
@endsection
```

#### Step 1.3: تحسين Review Create/Edit Forms

**الملف**: `resources/views/reviews/create.blade.php`

```blade
@extends('layouts.app')

@section('title', __('reviews.write_review'))

@section('content')
<div class="container mx-auto px-4 py-8 max-w-2xl">
    <div class="bg-white rounded-lg shadow-lg p-8">
        <!-- رأس النموذج -->
        <h1 class="text-3xl font-bold text-gray-900 mb-2">
            {{ __('reviews.write_review') }}
        </h1>
        <p class="text-gray-600 mb-8">
            {{ __('reviews.review_for') }} <strong>{{ $provider->user->name }}</strong>
        </p>

        <form action="{{ route('reviews.store') }}" method="POST" class="space-y-6">
            @csrf
            <input type="hidden" name="service_provider_profile_id" value="{{ $provider->id }}">

            <!-- نموذج التقييم التفاعلي -->
            <div>
                <label class="block text-lg font-semibold text-gray-900 mb-4">
                    {{ __('reviews.rating') }}
                    <span class="text-red-500">*</span>
                </label>

                <div class="flex gap-4 mb-4">
                    @for ($i = 1; $i <= 5; $i++)
                        <input type="radio" name="rating" value="{{ $i }}" id="rating_{{ $i }}" 
                               class="hidden peer" required>
                        <label for="rating_{{ $i }}" 
                               class="cursor-pointer text-4xl text-gray-300 peer-checked:text-yellow-400 hover:text-yellow-300 transition-colors">
                            ★
                        </label>
                    @endfor
                </div>

                <div id="ratingText" class="text-sm text-gray-600">
                    {{ __('reviews.select_rating') }}
                </div>

                @error('rating')
                    <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                @enderror
            </div>

            <!-- نص التقييم -->
            <div>
                <label for="review_text" class="block text-lg font-semibold text-gray-900 mb-2">
                    {{ __('reviews.review_text') }}
                    <span class="text-red-500">*</span>
                </label>

                <textarea name="review_text" id="review_text" rows="6" 
                          placeholder="{{ __('reviews.describe_experience') }}"
                          class="w-full border-2 border-gray-300 rounded-lg p-4 focus:outline-none focus:border-blue-500"
                          required>{{ old('review_text') }}</textarea>

                <p class="text-sm text-gray-500 mt-2">
                    <span id="charCount">0</span> / 1000 {{ __('general.characters') }}
                </p>

                @error('review_text')
                    <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                @enderror
            </div>

            <!-- الأزرار -->
            <div class="flex gap-4 pt-6 border-t border-gray-200">
                <button type="submit" 
                        class="flex-1 bg-gradient-to-r from-blue-600 to-purple-600 text-white px-6 py-3 rounded-lg font-semibold hover:shadow-lg transition-all">
                    <i class="fas fa-paper-plane mr-2"></i>
                    {{ __('reviews.submit_review') }}
                </button>

                <a href="{{ route('reviews.index', $provider) }}" 
                   class="flex-1 bg-gray-200 text-gray-800 px-6 py-3 rounded-lg font-semibold text-center hover:bg-gray-300 transition-colors">
                    {{ __('general.cancel') }}
                </a>
            </div>
        </form>

        <!-- ملخص التقييم -->
        <div class="mt-8 p-4 bg-blue-50 rounded-lg border border-blue-200">
            <p class="text-sm text-gray-700">
                <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                {{ __('reviews.review_note') }}
            </p>
        </div>
    </div>
</div>

<script>
    // Interactive rating selection
    document.querySelectorAll('input[name="rating"]').forEach(radio => {
        radio.addEventListener('change', function() {
            const ratings = {
                1: '{{ __("reviews.poor") }}',
                2: '{{ __("reviews.fair") }}',
                3: '{{ __("reviews.good") }}',
                4: '{{ __("reviews.very_good") }}',
                5: '{{ __("reviews.excellent") }}'
            };
            document.getElementById('ratingText').textContent = ratings[this.value];
        });
    });

    // Character counter
    document.getElementById('review_text')?.addEventListener('input', function() {
        document.getElementById('charCount').textContent = this.value.length;
    });
</script>
@endsection
```

---

### Phase 2: تطوير Comments كـ Frontend (4-6 ساعات)

#### Step 2.1: إنشاء Comment Card Component

**الملف**: `resources/views/components/comment-card.blade.php`

```blade
@props([
    'comment',
    'showStatus' => false,
    'showActions' => false,
])

<div class="comment-card bg-gray-50 rounded-lg border border-gray-200 p-4 hover:border-gray-300 transition-all">
    <div class="flex gap-4">
        <!-- Avatar -->
        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-green-500 to-blue-500 flex-shrink-0 flex items-center justify-center text-white font-bold">
            {{ strtoupper(substr($comment->user->name, 0, 1)) }}
        </div>

        <!-- المحتوى -->
        <div class="flex-1">
            <!-- رأس التعليق -->
            <div class="flex items-center justify-between mb-2">
                <div>
                    <p class="font-semibold text-gray-900">{{ $comment->user->name }}</p>
                    <p class="text-xs text-gray-500">{{ $comment->created_at->diffForHumans() }}</p>
                </div>

                @if ($showStatus && !$comment->is_active)
                    <span class="inline-flex items-center gap-1 bg-yellow-100 text-yellow-700 px-2 py-1 rounded text-xs font-medium">
                        <i class="fas fa-clock"></i>
                        {{ __('comments.pending') }}
                    </span>
                @endif
            </div>

            <!-- نص التعليق -->
            <p class="text-gray-700 text-sm">{{ $comment->content }}</p>

            <!-- الإجراءات -->
            @if ($showActions)
                <div class="flex gap-3 mt-3 pt-3 border-t border-gray-200">
                    @if (Auth::check() && Auth::user()->id === $comment->user_id && !$comment->is_active)
                        <a href="{{ route('comments.edit', $comment) }}" 
                           class="text-blue-600 hover:text-blue-800 text-xs font-medium">
                            <i class="fas fa-edit mr-1"></i>{{ __('general.edit') }}
                        </a>
                    @endif

                    @if (Auth::check() && (Auth::user()->id === $comment->user_id || Auth::user()->isAdmin()))
                        <form method="POST" action="{{ route('comments.destroy', $comment) }}" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-800 text-xs font-medium"
                                    onclick="return confirm('{{ __('comments.confirm_delete') }}?');">
                                <i class="fas fa-trash mr-1"></i>{{ __('general.delete') }}
                            </button>
                        </form>
                    @endif

                    @if (Auth::check() && Auth::user()->id !== $comment->user_id)
                        <form method="POST" action="{{ route('comments.flag', $comment) }}" class="inline">
                            @csrf
                            <button type="submit" class="text-amber-600 hover:text-amber-800 text-xs font-medium">
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

#### Step 2.2: إضافة Comments Section في Review Show

**الملف**: تحديث في Review Show - إضافة comments section

```blade
<!-- في نهاية review-card.blade.php أو show.blade.php -->

<div class="mt-6 pt-6 border-t border-gray-200">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">
        <i class="fas fa-comments mr-2 text-blue-600"></i>
        {{ __('comments.discussion') }}
    </h3>

    <!-- Comments List -->
    @if ($review->activeComments()->count() > 0)
        <div class="space-y-3 mb-6">
            @foreach ($review->activeComments as $comment)
                <x-comment-card :comment="$comment" 
                               :showStatus="Auth::check()" 
                               :showActions="Auth::check()" />
            @endforeach
        </div>
    @endif

    <!-- Add Comment Form -->
    @auth
        @if (Auth::user()->isClient())
            <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                <form action="{{ route('comments.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="commentable_type" value="App\Models\Review">
                    <input type="hidden" name="commentable_id" value="{{ $review->id }}">

                    <textarea name="content" placeholder="{{ __('comments.write_comment') }}" 
                              rows="3" 
                              class="w-full border-2 border-gray-300 rounded-lg p-3 focus:outline-none focus:border-blue-500 text-sm"></textarea>

                    <div class="flex justify-end gap-2 mt-3">
                        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded font-medium hover:bg-blue-700 transition-colors">
                            {{ __('comments.post_comment') }}
                        </button>
                    </div>
                </form>
            </div>
        @endif
    @endauth
</div>
```

---

### Phase 3: تحسين الفلاترات (4-5 ساعات)

#### Step 3.1: إضافة Scope Methods في ServiceProvider Model

**الملف**: `app/Models/ServiceProvider.php`

```php
// إضافة الـ scopes الجديدة:

public function scopeWithAverageRating($query)
{
    return $query->withCount('reviews')
        ->withAvg('reviews', 'rating');
}

public function scopeFilterByMinRating($query, $minRating)
{
    return $query->whereHas('reviews', function ($q) {
        $q->where('is_active', true);
    }, '>=', 1)
    ->havingAvg('reviews.rating', '>=', $minRating);
}

public function scopeFilterByVerifiedReviews($query)
{
    return $query->whereHas('reviews', function ($q) {
        $q->where('is_active', true)
          ->where('is_verified', true);
    });
}

public function scopeOrderByRating($query)
{
    return $query->orderByRaw('(SELECT AVG(rating) FROM service_provider_reviews WHERE service_provider_id = service_providers.id AND is_active = 1) DESC');
}

public function scopeOrderByReviewCount($query)
{
    return $query->withCount('reviews')
        ->orderByDesc('reviews_count');
}

// Properties للـ computed values:
public function getAverageRatingAttribute()
{
    return $this->reviews()
        ->where('is_active', true)
        ->avg('rating') ?? 0;
}

public function getReviewsCountAttribute()
{
    return $this->reviews()
        ->where('is_active', true)
        ->count();
}

public function getVerifiedReviewsCountAttribute()
{
    return $this->reviews()
        ->where('is_active', true)
        ->where('is_verified', true)
        ->count();
}
```

#### Step 3.2: تحديث ServiceProviderController - index()

**الملف**: `app/Http/Controllers/ServiceProviderController.php`

```php
public function index(Request $request)
{
    $query = ServiceProvider::query();

    // البحث والتصفية الأساسية
    if ($request->filled('search')) {
        $search = $request->input('search');
        $query->where(function ($q) use ($search) {
            $q->where('company_name', 'LIKE', "%{$search}%")
                ->orWhere('bio', 'LIKE', "%{$search}%");
        });
    }

    // فلتر الفئة
    if ($request->filled('category')) {
        $query->where('category_id', $request->input('category'));
    }

    // فلتر الموقع
    if ($request->filled('location')) {
        $query->where('location_id', $request->input('location'));
    }

    // ===== الفلاترات الجديدة =====

    // فلتر التقييمات (Minimum Rating)
    if ($request->filled('min_rating')) {
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

    // Sorting
    $sortBy = $request->input('sort_by', 'rating');
    switch ($sortBy) {
        case 'rating_asc':
            $query->orderByRating();
            break;
        case 'rating_desc':
            $query->orderByRating();
            break;
        case 'reviews_count':
            $query->orderByReviewCount();
            break;
        case 'newest':
            $query->latest('created_at');
            break;
        default: // 'rating'
            $query->orderByRating();
    }

    $serviceProviders = $query->with(['category', 'location', 'reviews'])
        ->paginate(12)
        ->withQueryString();

    // البيانات للـ Selects
    $categories = Category::active()->orderBy('name')->get();
    $locations = Location::active()->orderBy('city')->get();
    $revealedContacts = session('revealed_contacts', []);

    return view('service-providers.index', compact(
        'serviceProviders',
        'categories',
        'locations',
        'revealedContacts'
    ));
}
```

#### Step 3.3: تحديث Service Providers Index View - الفلاترات

**الملف**: تحديث `resources/views/service-providers/index.blade.php`

```blade
<!-- إضافة الفلاترات الجديدة في Hero Section -->

<div class="filter-panel bg-white rounded-lg p-6 border border-gray-200 mb-6">
    <form method="GET" action="{{ route('service-providers.index') }}" class="space-y-4">
        <!-- الفلاترات الموجودة -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Search -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">{{ __('general.search') }}</label>
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="{{ __('general.search_providers') }}"
                       class="w-full border-2 border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:border-blue-500">
            </div>

            <!-- Category -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">{{ __('categories.category') }}</label>
                <select name="category" class="w-full border-2 border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:border-blue-500">
                    <option value="">{{ __('general.all_categories') }}</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Location -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">{{ __('locations.location') }}</label>
                <select name="location" class="w-full border-2 border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:border-blue-500">
                    <option value="">{{ __('general.all_locations') }}</option>
                    @foreach ($locations as $location)
                        <option value="{{ $location->id }}" {{ request('location') == $location->id ? 'selected' : '' }}>
                            {{ $location->city }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Sorting -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">{{ __('general.sort_by') }}</label>
                <select name="sort_by" class="w-full border-2 border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:border-blue-500">
                    <option value="rating" {{ request('sort_by', 'rating') == 'rating' ? 'selected' : '' }}>
                        {{ __('reviews.highest_rated') }}
                    </option>
                    <option value="reviews_count" {{ request('sort_by') == 'reviews_count' ? 'selected' : '' }}>
                        {{ __('reviews.most_reviewed') }}
                    </option>
                    <option value="newest" {{ request('sort_by') == 'newest' ? 'selected' : '' }}>
                        {{ __('general.newest') }}
                    </option>
                </select>
            </div>
        </div>

        <!-- الفلاترات الجديدة -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-4 border-t border-gray-200">
            <!-- Minimum Rating -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">{{ __('reviews.minimum_rating') }}</label>
                <div class="flex gap-2 items-center">
                    @for ($i = 0; $i <= 5; $i++)
                        <label class="flex items-center gap-1 cursor-pointer">
                            <input type="radio" name="min_rating" value="{{ $i }}" 
                                   {{ request('min_rating', 0) == $i ? 'checked' : '' }}>
                            @if ($i == 0)
                                {{ __('general.all') }}
                            @else
                                <span class="text-yellow-400">{{ str_repeat('★', $i) }}</span>
                                {{ $i }}+
                            @endif
                        </label>
                    @endfor
                </div>
            </div>

            <!-- Verified Reviews Only -->
            <div>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="verified_only" value="1" 
                           {{ request('verified_only') ? 'checked' : '' }}>
                    <span class="text-sm font-semibold text-gray-700">
                        <i class="fas fa-check-circle text-green-600 mr-1"></i>
                        {{ __('reviews.verified_reviews_only') }}
                    </span>
                </label>
            </div>

            <!-- Buttons -->
            <div class="flex gap-2 justify-end items-end">
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg font-semibold hover:bg-blue-700 transition-colors">
                    <i class="fas fa-search mr-2"></i>{{ __('general.filter') }}
                </button>
                <a href="{{ route('service-providers.index') }}" class="bg-gray-300 text-gray-800 px-6 py-2 rounded-lg font-semibold hover:bg-gray-400 transition-colors">
                    {{ __('general.reset') }}
                </a>
            </div>
        </div>
    </form>
</div>

<!-- تحديث Service Provider Card - إضافة Rating Info -->
@foreach ($serviceProviders as $provider)
    <div class="provider-card bg-white rounded-lg shadow-md hover:shadow-lg transition-all overflow-hidden">
        <!-- الصورة -->
        <div class="relative h-48 bg-gray-200 overflow-hidden">
            @if ($provider->profile_image)
                <img src="{{ Storage::url($provider->profile_image) }}" alt="{{ $provider->company_name }}" class="w-full h-full object-cover">
            @endif
        </div>

        <!-- المعلومات -->
        <div class="p-4">
            <h3 class="text-lg font-bold text-gray-900">{{ $provider->company_name }}</h3>

            <!-- التقييم والعدد -->
            <div class="flex items-center justify-between my-3">
                <div class="flex items-center gap-2">
                    <div class="flex text-yellow-400">
                        @for ($i = 1; $i <= 5; $i++)
                            <span>{{ $i <= round($provider->average_rating) ? '★' : '☆' }}</span>
                        @endfor
                    </div>
                    <span class="text-sm font-semibold text-gray-700">
                        {{ number_format($provider->average_rating, 1) }}/5
                    </span>
                </div>

                <span class="text-xs bg-blue-100 text-blue-700 px-2 py-1 rounded">
                    {{ $provider->reviews_count }} {{ __('reviews.reviews') }}
                </span>
            </div>

            <!-- معلومات أخرى -->
            <p class="text-sm text-gray-600 mb-3">{{ Str::limit($provider->bio, 100) }}</p>

            <!-- الأزرار -->
            <div class="flex gap-2">
                <a href="{{ route('service-providers.show', $provider) }}" 
                   class="flex-1 bg-blue-600 text-white text-center px-3 py-2 rounded font-medium hover:bg-blue-700 transition-colors">
                    {{ __('general.view') }}
                </a>

                <a href="{{ route('reviews.index', $provider) }}" 
                   class="flex-1 bg-yellow-50 text-yellow-700 text-center px-3 py-2 rounded font-medium border border-yellow-200 hover:bg-yellow-100 transition-colors">
                    <i class="fas fa-star mr-1"></i>{{ __('reviews.reviews') }}
                </a>
            </div>
        </div>
    </div>
@endforeach
```

---

## 📊 ملفات الترجمة الجديدة

**الملفات المطلوبة**: تحديث `lang/ar/*.php` و `lang/en/*.php`

```php
// lang/en/reviews.php - الإضافات:
'average_rating' => 'Average Rating',
'total_reviews' => 'Total Reviews',
'verified' => 'Verified',
'rating_distribution' => 'Rating Distribution',
'no_reviews_yet' => 'No reviews yet',
'be_first_to_review' => 'Be the first one to review',
'write_review' => 'Write a Review',
'login_to_review' => 'Login to write a review',
'poor' => 'Poor',
'fair' => 'Fair',
'good' => 'Good',
'very_good' => 'Very Good',
'excellent' => 'Excellent',
'review_for' => 'Review for',
'describe_experience' => 'Describe your experience...',
'submit_review' => 'Submit Review',
'review_note' => 'Your review will be published after admin approval',
'view_reviews_for' => 'Reviews for',
'showing_active_reviews' => 'Showing approved reviews',
'verified_purchase' => 'Verified Purchase',
'featured_review' => 'Featured Review',
'highest_rated' => 'Highest Rated',
'most_reviewed' => 'Most Reviewed',
'minimum_rating' => 'Minimum Rating',
'verified_reviews_only' => 'Verified Reviews Only',
```

---

## 🧪 الاختبارات المطلوبة

```php
// tests/Feature/ReviewFilteringTest.php
<?php

class ReviewFilteringTest extends TestCase {
    public function test_filter_providers_by_minimum_rating() { ... }
    public function test_filter_providers_by_verified_reviews() { ... }
    public function test_sort_providers_by_rating() { ... }
    public function test_sort_providers_by_review_count() { ... }
}

// tests/Feature/ReviewsAndCommentsTest.php
<?php

class ReviewsAndCommentsTest extends TestCase {
    public function test_client_can_write_review() { ... }
    public function test_review_requires_approval() { ... }
    public function test_only_client_can_comment() { ... }
    public function test_comments_show_on_review() { ... }
}
```

---

## 📅 الجدول الزمني

| اليوم | المدة | المهام |
|------|------|--------|
| اليوم 1 | 4-6 س | Review UI/UX improvements (Card, Index, Forms) |
| اليوم 2 | 4-6 س | Comments integration + Scopes |
| اليوم 3 | 4-5 س | Filtering + Sorting + Translations |
| اليوم 4 | 2-3 س | Testing + QA + Bug Fixes |

**الإجمالي**: 12-16 ساعة = 2-2.5 أيام عمل

---

## ✅ Checklist التنفيذ

- [ ] إنشاء Review Card Component
- [ ] تحديث Reviews Index View
- [ ] تحسين Create/Edit Forms
- [ ] إنشاء Comment Card Component
- [ ] إضافة Comments Section في Reviews
- [ ] إضافة Scopes في Models
- [ ] تحديث Controller Methods
- [ ] تحديث Filter UI
- [ ] إضافة الترجمات
- [ ] إنشاء Tests
- [ ] QA والاختبار
- [ ] النشر على Staging

---

**الحالة**: جاهزة للتنفيذ الفوري ✅
