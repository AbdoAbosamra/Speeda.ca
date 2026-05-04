<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BlogPostController extends Controller
{
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
        $post->delete();
        $this->clearBlogCaches();

        return redirect()
            ->route('admin.blog.posts.index')
            ->with('success', 'Blog post deleted safely.');
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
            'status' => $status,
            'is_published' => $status === 'published',
            'published_at' => $publishedAt,
            'allow_indexing' => $request->boolean('allow_indexing', true),
            'author_id' => $post->author_id ?: Auth::id(),
            'reading_time_minutes' => max(1, (int) ceil(str_word_count(strip_tags($contentEn)) / 220)),
        ];

        if ($request->hasFile('featured_image')) {
            $data['featured_image'] = $request->file('featured_image')->store('blog-images', 'public');
            $data['image'] = $data['featured_image'];
        }

        $post->fill($this->onlyExistingColumns($data));
        $post->save();
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

    protected function onlyExistingColumns(array $data): array
    {
        return collect($data)
            ->filter(fn ($value, $column) => Schema::hasColumn('posts', $column))
            ->all();
    }

    protected function categories()
    {
        return Category::query()
            ->where('is_active', true)
            ->orderBy('name_en')
            ->orderBy('name')
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
