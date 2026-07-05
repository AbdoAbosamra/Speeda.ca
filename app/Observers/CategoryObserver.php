<?php

namespace App\Observers;

use App\Models\Category;
use App\Domain\SEO\Services\SeoMetaService;

class CategoryObserver
{
    public function saved(Category $category): void
    {
        app(SeoMetaService::class)->invalidate('category', $category->id);
    }

    public function deleted(Category $category): void
    {
        app(SeoMetaService::class)->invalidate('category', $category->id);
    }
}
