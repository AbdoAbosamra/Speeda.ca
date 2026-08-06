<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Location;
use App\Models\Post;
use App\Traits\HandlesBulkActions;
use App\Traits\LogsAdminActions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BlogPostController extends Controller
{
    use LogsAdminActions;
    use HandlesBulkActions;

    public function bulk(Request $request)
    {
        return $this->runBulkAction($request, 'posts');
    }

    protected function bulkActions(string $resource): array
    {
        return [
            'publish' => __('admin.bulk_verb_published'),
            'draft' => __('admin.bulk_verb_drafted'),
            'delete' => __('admin.bulk_verb_trashed'),
            'restore' => __('admin.bulk_verb_restored'),
        ];
    }

    protected function bulkQuery(string $resource): \Illuminate\Database\Eloquent\Builder
    {
        // withTrashed so the Trash tab can restore in bulk.
        return Post::withTrashed();
    }

    /**
     * @return true|string
     */
    protected function applyBulkAction(string $resource, string $action, $post)
    {
        if ($post->trashed() && $action !== 'restore') {
            return __('admin.bulk_reason_already_trashed');
        }

        switch ($action) {
            case 'publish':
                if ($post->status === 'published') {
                    return __('admin.bulk_reason_already_published');
                }
                $old = $post->getOriginal();
                $post->update([
                    'status' => 'published',
                    'is_published' => true,
                    'published_at' => $post->published_at ?: now(),
                ]);
                $this->logUpdate($post, $old);
                break;

            case 'draft':
                if ($post->status === 'draft') {
                    return __('admin.bulk_reason_already_draft');
                }
                $old = $post->getOriginal();
                $post->update(['status' => 'draft', 'is_published' => false]);
                $this->logUpdate($post, $old);
                break;

            case 'delete':
                $this->logAction('delete', $post, ['deleted' => ['title' => $post->title, 'slug' => $post->slug]]);
                $post->delete();
                break;

            case 'restore':
                if (!$post->trashed()) {
                    return __('admin.bulk_reason_not_trashed');
                }
                $post->restore();
                $this->logAction('restore', $post);
                break;

            default:
                return __('admin.bulk_reason_failed');
        }

        $this->clearBlogCaches();

        return true;
    }

    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $status = (string) $request->query('status', '');

        $posts = Post::query()
            ->with(['author', 'category'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($builder) use ($search) {
                    $builder->where('title', 'like', "%{$search}%")
                        ->orWhere('title_en', 'like', "%{$search}%")
                        ->orWhere('title_ar', 'like', "%{$search}%")
                        ->orWhere('title_fr', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%");
                });
            })
            ->when(in_array($status, ['draft', 'published'], true), fn ($query) => $query->where('status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $counts = [
            'total' => Post::count(),
            'published' => Post::where('status', 'published')->count(),
            'draft' => Post::where('status', 'draft')->count(),
        ];

        return view('admin.blog.posts.index', compact('posts', 'counts', 'search', 'status'));
    }

    public function create(): View
    {
        return view('admin.blog.posts.create', [
            'post' => new Post(['status' => 'draft', 'allow_indexing' => true]),
            'categories' => $this->categories(),
            'locations' => $this->locations(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatePost($request);
        $post = new Post();

        $this->fillPost($post, $validated, $request);

        return redirect()
            ->route('admin.blog.posts.edit', $post)
            ->with('success', 'Blog post created successfully.');
    }

    public function edit(Post $post): View
    {
        return view('admin.blog.posts.edit', [
            'post' => $post,
            'categories' => $this->categories(),
            'locations' => $this->locations(),
        ]);
    }

    public function update(Request $request, Post $post): RedirectResponse
    {
        $validated = $this->validatePost($request, $post);
        $this->fillPost($post, $validated, $request);

        return redirect()
            ->route('admin.blog.posts.edit', $post)
            ->with('success', 'Blog post updated successfully.');
    }

    public function destroy(Post $post): RedirectResponse
    {
        $this->logAction('delete', $post, ['deleted' => ['title' => $post->title, 'slug' => $post->slug]]);

        // Soft delete only, so the cover image is deliberately left on disk —
        // restoring from Trash needs it.
        $post->delete();
        $this->clearBlogCaches();

        return redirect()
            ->route('admin.blog.posts.index')
            ->with('success', 'Blog post deleted safely. You can restore it from the Trash tab.');
    }

    /**
     * Soft-deleted posts, so a mistaken delete is recoverable.
     */
    public function trash(): View
    {
        $posts = Post::onlyTrashed()
            ->with(['author', 'category'])
            ->latest('deleted_at')
            ->paginate(15);

        return view('admin.blog.posts.trash', compact('posts'));
    }

    public function restore(int $postId): RedirectResponse
    {
        $post = Post::withTrashed()->findOrFail($postId);
        $post->restore();

        $this->logAction('restore', $post);
        $this->clearBlogCaches();

        return redirect()
            ->route('admin.blog.posts.index')
            ->with('success', 'Blog post restored.');
    }

    protected function validatePost(Request $request, ?Post $post = null): array
    {
        return $request->validate([
            'title_en' => ['required', 'string', 'max:255'],
            'title_ar' => ['required', 'string', 'max:255'],
            'title_fr' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('posts', 'slug')->ignore($post?->id),
            ],
            'content_en' => ['required', 'string'],
            'content_ar' => ['required', 'string'],
            'content_fr' => ['required', 'string'],
            'excerpt_en' => ['nullable', 'string', 'max:500'],
            'excerpt_ar' => ['nullable', 'string', 'max:500'],
            'excerpt_fr' => ['nullable', 'string', 'max:500'],
            'seo_title_en' => ['nullable', 'string', 'max:255'],
            'seo_title_ar' => ['nullable', 'string', 'max:255'],
            'seo_title_fr' => ['nullable', 'string', 'max:255'],
            'seo_description_en' => ['nullable', 'string', 'max:500'],
            'seo_description_ar' => ['nullable', 'string', 'max:500'],
            'seo_description_fr' => ['nullable', 'string', 'max:500'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'location_id' => ['nullable', 'exists:locations,id'],
            'status' => ['required', Rule::in(['draft', 'published'])],
            'published_at' => ['nullable', 'date'],
            'featured_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],
            'allow_indexing' => ['nullable', 'boolean'],
        ]);
    }

    protected function fillPost(Post $post, array $validated, Request $request): void
    {
        $titleEn = trim($validated['title_en']);
        $titleAr = trim($validated['title_ar']);
        $titleFr = trim($validated['title_fr']);
        $contentEn = trim($validated['content_en']);
        $contentAr = trim($validated['content_ar']);
        $contentFr = trim($validated['content_fr']);
        $excerptEn = $validated['excerpt_en'] ?: Str::limit(strip_tags($contentEn), 180);
        $excerptAr = $validated['excerpt_ar'] ?: Str::limit(strip_tags($contentAr), 180);
        $excerptFr = $validated['excerpt_fr'] ?: Str::limit(strip_tags($contentFr), 180);
        $status = $validated['status'];
        $publishedAt = $status === 'published'
            ? ($validated['published_at'] ?? now())
            : ($validated['published_at'] ?? null);

        $data = [
            'title' => $titleEn,
            'title_en' => $titleEn,
            'title_ar' => $titleAr,
            'title_fr' => $titleFr,
            'slug' => $this->uniqueSlug($validated['slug'] ?: $titleEn, $post->exists ? $post->id : null),
            'content' => $contentEn,
            'content_en' => $contentEn,
            'content_ar' => $contentAr,
            'content_fr' => $contentFr,
            'excerpt' => $excerptEn,
            'excerpt_en' => $excerptEn,
            'excerpt_ar' => $excerptAr,
            'excerpt_fr' => $excerptFr,
            'seo_title_en' => $validated['seo_title_en'] ?: $titleEn,
            'seo_title_ar' => $validated['seo_title_ar'] ?: $titleAr,
            'seo_title_fr' => $validated['seo_title_fr'] ?: $titleFr,
            'seo_description_en' => $validated['seo_description_en'] ?: $excerptEn,
            'seo_description_ar' => $validated['seo_description_ar'] ?: $excerptAr,
            'seo_description_fr' => $validated['seo_description_fr'] ?: $excerptFr,
            'category_id' => $validated['category_id'] ?? null,
            'location_id' => $validated['location_id'] ?? null,
            'status' => $status,
            'is_published' => $status === 'published',
            'published_at' => $publishedAt,
            'allow_indexing' => $request->boolean('allow_indexing', true),
            'author_id' => $post->author_id ?: Auth::id(),
            'reading_time_minutes' => max(1, (int) ceil(str_word_count(strip_tags($contentEn)) / 220)),
        ];

        if ($request->hasFile('featured_image')) {
            $previousImage = $post->featured_image;

            $data['featured_image'] = $request->file('featured_image')->store('blog-images', 'public');
            $data['image'] = $data['featured_image'];

            // Remove the superseded file so replacing a cover image repeatedly
            // does not leave orphans piling up on disk.
            if ($previousImage
                && $previousImage !== $data['featured_image']
                && Storage::disk('public')->exists($previousImage)) {
                Storage::disk('public')->delete($previousImage);
            }
        }

        $isNew = !$post->exists;
        $oldValues = $post->getOriginal();

        $post->fill($this->onlyExistingColumns($data));
        $post->save();

        // Blog edits now land in the admin activity log like every other module.
        $isNew ? $this->logCreate($post) : $this->logUpdate($post, $oldValues);

        $this->clearBlogCaches();
    }

    protected function uniqueSlug(string $value, ?int $ignoreId = null): string
    {
        $base = Str::slug($value) ?: 'blog-post';
        $slug = $base;
        $counter = 2;

        while (
            Post::where('slug', $slug)
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = "{$base}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    /**
     * Drop keys that have no matching column on the posts table.
     *
     * The column list is fetched once and cached: the previous implementation
     * called Schema::hasColumn() per key, firing ~23 metadata queries on every
     * single save.
     */
    protected function onlyExistingColumns(array $data): array
    {
        $columns = Cache::remember(
            'schema.posts.columns',
            now()->addDay(),
            fn () => Schema::getColumnListing('posts')
        );

        return array_intersect_key($data, array_flip($columns));
    }

    protected function categories()
    {
        $othersNames = ['others', 'other', 'أخرى'];

        return Category::query()
            ->active()
            ->where(function($q) {
                $q->where('is_section', false)
                  ->orWhereDoesntHave('allChildren');
            })
            ->get()
            ->sort(function ($a, $b) use ($othersNames) {
                $aName = strtolower(trim($a->localized_name));
                $bName = strtolower(trim($b->localized_name));
                
                $aIsOthers = in_array($aName, $othersNames);
                $bIsOthers = in_array($bName, $othersNames);
                
                if ($aIsOthers && !$bIsOthers) return 1;
                if (!$aIsOthers && $bIsOthers) return -1;
                
                return strcmp($aName, $bName);
            });
    }

    protected function locations()
    {
        return Location::query()
            ->active()
            ->orderBy('city')
            ->get();
    }

    protected function clearBlogCaches(): void
    {
        Cache::forget('home_latest_blog_posts');
        Cache::forget('blog_featured_posts_' . app()->getLocale());
        Cache::forget('blog_featured_posts_en');
        Cache::forget('blog_featured_posts_ar');
        Cache::forget('blog_featured_posts_fr');
    }
}
