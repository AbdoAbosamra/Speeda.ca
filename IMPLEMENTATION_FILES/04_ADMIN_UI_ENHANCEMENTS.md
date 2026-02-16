# Admin UI Enhancements for Category Translation

## Overview
This document provides the complete code for enhancing the admin category forms with:
1. Translation completeness indicators
2. Auto-translate buttons (if Google API available)
3. Better visual feedback
4. Translation status badges

## File: resources/views/admin/categories/index.blade.php

### Add to Create Category Modal (around line 250)

```blade
<!-- Language Tabs -->
<div class="nav nav-pills nav-fill mb-4 bg-light p-2 rounded-pill">
    <button type="button" class="nav-link rounded-pill" :class="{ 'active bg-primary text-white': activeTab === 'ar' }" @click="activeTab = 'ar'">
        🇸🇦 {{ __('admin.arabic') }} <span class="badge bg-danger ms-1">*</span>
    </button>
    <button type="button" class="nav-link rounded-pill" :class="{ 'active bg-primary text-white': activeTab === 'en' }" @click="activeTab = 'en'">
        🇬🇧 {{ __('admin.english') }} <span class="badge bg-danger ms-1">*</span>
    </button>
    <button type="button" class="nav-link rounded-pill" :class="{ 'active bg-primary text-white': activeTab === 'fr' }" @click="activeTab = 'fr'">
        🇫🇷 {{ __('admin.french') }} <span class="badge bg-danger ms-1">*</span>
    </button>
</div>

<!-- Arabic Fields -->
<div x-show="activeTab === 'ar'" x-transition>
    <div class="mb-3">
        <label class="form-label fw-semibold">
            {{ __('admin.name_ar') }} 
            <span class="text-danger">*</span>
        </label>
        <input type="text" name="name_ar" class="form-control" required>
    </div>
    <div class="mb-3">
        <label class="form-label fw-semibold">{{ __('admin.description_ar') }}</label>
        <textarea name="description_ar" class="form-control" rows="3"></textarea>
    </div>
</div>

<!-- English Fields -->
<div x-show="activeTab === 'en'" x-transition style="display: none;">
    <div class="mb-3">
        <label class="form-label fw-semibold">
            {{ __('admin.name_en') }} 
            <span class="text-danger">*</span>
            @if(config('services.google_translate.api_key'))
                <button type="button" class="btn btn-sm btn-outline-primary ms-2" onclick="autoTranslate('en')" title="{{ __('admin.auto_translate') }}">
                    <i class="fas fa-magic me-1"></i> {{ __('admin.auto_translate') }}
                </button>
            @endif
        </label>
        <input type="text" name="name_en" class="form-control" required>
        <small class="text-muted">{{ __('admin.slug_generated_from_en') }}</small>
    </div>
    <div class="mb-3">
        <label class="form-label fw-semibold">{{ __('admin.description_en') }}</label>
        <textarea name="description_en" class="form-control" rows="3"></textarea>
    </div>
</div>

<!-- French Fields -->
<div x-show="activeTab === 'fr'" x-transition style="display: none;">
    <div class="mb-3">
        <label class="form-label fw-semibold">
            {{ __('admin.name_fr') }} 
            <span class="text-danger">*</span>
            @if(config('services.google_translate.api_key'))
                <button type="button" class="btn btn-sm btn-outline-primary ms-2" onclick="autoTranslate('fr')" title="{{ __('admin.auto_translate') }}">
                    <i class="fas fa-magic me-1"></i> {{ __('admin.auto_translate') }}
                </button>
            @endif
        </label>
        <input type="text" name="name_fr" class="form-control" required>
    </div>
    <div class="mb-3">
        <label class="form-label fw-semibold">{{ __('admin.description_fr') }}</label>
        <textarea name="description_fr" class="form-control" rows="3"></textarea>
    </div>
</div>
```

### Add JavaScript for Auto-Translate (before closing </body> tag)

```blade
<script>
function autoTranslate(targetLang) {
    const nameAr = document.querySelector('input[name="name_ar"]').value;
    const descAr = document.querySelector('textarea[name="description_ar"]').value;
    
    if (!nameAr) {
        alert('{{ __('admin.enter_arabic_first') }}');
        return;
    }
    
    // Show loading
    const button = event.target.closest('button');
    const originalText = button.innerHTML;
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> {{ __('admin.translating') }}...';
    
    // Call API
    fetch('{{ route('admin.categories.auto-translate') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            text: nameAr,
            target_language: targetLang
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.translated_text) {
            if (targetLang === 'en') {
                document.querySelector('input[name="name_en"]').value = data.translated_text;
            } else if (targetLang === 'fr') {
                document.querySelector('input[name="name_fr"]').value = data.translated_text;
            }
        } else {
            alert('{{ __('admin.translation_failed') }}');
        }
    })
    .catch(error => {
        console.error('Translation error:', error);
        alert('{{ __('admin.translation_error') }}');
    })
    .finally(() => {
        button.disabled = false;
        button.innerHTML = originalText;
    });
}
</script>
```

## File: resources/views/admin/categories/edit.blade.php

### Similar changes to edit form (add required indicators and auto-translate buttons)

Same pattern as create form, but with existing values pre-filled.

## File: app/Http/Controllers/Admin/AdminController.php

### Add Auto-Translate Route Handler

```php
/**
 * Auto-translate text using Google Translate API
 */
public function autoTranslate(Request $request)
{
    $request->validate([
        'text' => 'required|string|max:500',
        'target_language' => 'required|in:en,fr',
    ]);

    try {
        $translationService = app(\App\Services\TranslationService::class);
        $translated = $translationService->translate($request->text, $request->target_language);

        if ($translated) {
            return response()->json([
                'success' => true,
                'translated_text' => $translated,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => __('admin.translation_failed'),
        ], 400);
    } catch (\Exception $e) {
        Log::error('Auto-translate error', [
            'error' => $e->getMessage(),
            'text' => $request->text,
            'target' => $request->target_language,
        ]);

        return response()->json([
            'success' => false,
            'message' => __('admin.translation_error'),
        ], 500);
    }
}
```

### Add Route (routes/web.php or routes/admin.php)

```php
Route::post('/admin/categories/auto-translate', [AdminController::class, 'autoTranslate'])
    ->name('admin.categories.auto-translate')
    ->middleware(['auth', 'admin']);
```

## Translation Keys to Add

Add to `lang/en/admin.php`, `lang/ar/admin.php`, `lang/fr/admin.php`:

```php
'auto_translate' => 'Auto-translate',
'enter_arabic_first' => 'Please enter Arabic text first',
'translating' => 'Translating',
'translation_failed' => 'Translation failed. Please enter manually.',
'translation_error' => 'Translation service error. Please try again or enter manually.',
'category_name_ar_required' => 'Arabic name is required',
'category_name_en_required' => 'English name is required',
'category_name_fr_required' => 'French name is required',
'category_name_min' => 'Category name must be at least 2 characters',
```
