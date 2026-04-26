<?php

namespace Database\Seeders;

use App\Models\Post;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PostSeeder extends Seeder
{
    public function run(): void
    {
        $blogs = [
            [
                'title' => 'Top 10 Tips for Home Renovation',
                'content' => 'Renovating your home can be a daunting task, but with the right planning, it can also be incredibly rewarding. Start by defining your budget and then focus on high-impact areas like the kitchen and bathroom...',
                'excerpt' => 'Renovating your home can be a daunting task, but with the right planning, it can also be rewarding.',
                'category_id' => 1,
                'image' => 'https://images.unsplash.com/photo-1581578731548-c64695cc6952?q=80&w=800&auto=format&fit=crop',
            ],
            [
                'title' => 'Why Professional Plumbing Matters',
                'content' => 'A small leak might seem insignificant, but it can lead to massive water bills and structural damage over time. Hiring a professional plumber ensures that the job is done right the first time...',
                'excerpt' => 'A small leak might seem insignificant, but it can lead to massive water bills and structural damage.',
                'category_id' => 2,
                'image' => 'https://images.unsplash.com/photo-1504148455328-c376907d081c?q=80&w=800&auto=format&fit=crop',
            ],
            [
                'title' => 'Electrical Safety at Home',
                'content' => 'Electricity is an essential part of modern life, but it can also be dangerous if not handled properly. Learn the basics of electrical safety, from avoiding overloaded outlets to recognizing faulty wiring...',
                'excerpt' => 'Electricity is essential, but it can also be dangerous if not handled properly.',
                'category_id' => 3,
                'image' => 'https://images.unsplash.com/photo-1621905252507-b35492cc74b4?q=80&w=800&auto=format&fit=crop',
            ],
            [
                'title' => 'The Future of Smart Homes',
                'content' => 'Smart technology is changing the way we interact with our homes. From voice-activated lighting to intelligent security systems, the future of home automation is here...',
                'excerpt' => 'Smart technology is changing the way we interact with our homes. The future is here.',
                'category_id' => 1,
                'image' => 'https://images.unsplash.com/photo-1558002038-103792e17734?q=80&w=800&auto=format&fit=crop',
            ],
            [
                'title' => 'Maintaining Your HVAC System',
                'content' => 'Regular maintenance of your heating, ventilation, and air conditioning system is key to its longevity and efficiency. Discover simple tasks you can do to keep your home comfortable all year round...',
                'excerpt' => 'Regular maintenance of your HVAC system is key to its longevity and efficiency.',
                'category_id' => 2,
                'image' => 'https://images.unsplash.com/photo-1581094288338-2314dddb7ece?q=80&w=800&auto=format&fit=crop',
            ],
            [
                'title' => 'Sustainable Living Tips',
                'content' => 'Living a sustainable lifestyle doesn\'t have to be difficult. From reducing waste to choosing energy-efficient appliances, there are many small changes you can make to help the environment...',
                'excerpt' => 'Living a sustainable lifestyle doesn\'t have to be difficult. Start with small changes.',
                'category_id' => 3,
                'image' => 'https://images.unsplash.com/photo-1473341304170-971dccb5ac1e?q=80&w=800&auto=format&fit=crop',
            ],
        ];

        foreach ($blogs as $blog) {
            Post::create([
                'title' => $blog['title'],
                'slug' => Str::slug($blog['title']),
                'content' => $blog['content'],
                'excerpt' => $blog['excerpt'],
                'category_id' => $blog['category_id'],
                'image' => $blog['image'],
                'is_published' => true,
            ]);
        }
    }
}
