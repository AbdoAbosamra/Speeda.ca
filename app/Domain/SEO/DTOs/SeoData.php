<?php

namespace App\Domain\SEO\DTOs;

class SeoData
{
    public function __construct(
        public string $title,
        public ?string $description = null,
        public ?string $keywords = null,
        public ?string $ogImage = null,
        public string $ogType = 'website',
        public ?string $canonical = null,
        public array $hreflangs = [],
        public string $robots = 'index, follow'
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            title: $data['title'] ?? config('app.name'),
            description: $data['description'] ?? null,
            keywords: $data['keywords'] ?? null,
            ogImage: $data['og_image'] ?? null,
            ogType: $data['og_type'] ?? 'website',
            canonical: $data['canonical'] ?? null,
            hreflangs: $data['hreflangs'] ?? [],
            robots: $data['robots'] ?? 'index, follow'
        );
    }
}
