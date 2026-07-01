<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BlogSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $rows = [
            ['title' => 'Why Palm Leaf Plates Are Replacing Single-Use Plastics', 'category' => 'Sustainability'],
            ['title' => 'How to Plan a Zero-Waste Outdoor Celebration', 'category' => 'Eco Living'],
            ['title' => 'A Buyer Guide to Compostable Dinnerware for Restaurants', 'category' => 'Products'],
            ['title' => '5 Food-Safe Benefits of Natural Areca Palm Tableware', 'category' => 'Tips'],
            ['title' => 'How NatureOne Supports Greener Supply Chains', 'category' => 'News'],
            ['title' => 'Bulk Order Checklist for Wedding Caterers', 'category' => 'Tips'],
            ['title' => 'Understanding Compostability Standards in India', 'category' => 'Sustainability'],
        ];

        foreach ($rows as $index => $row) {
            $slug = Str::slug($row['title']);
            $publishDate = $now->copy()->subDays($index + 1);
            DB::table('blogs')->insert([
                'title' => $row['title'],
                'slug' => $slug,
                'description' => 'Practical insights on sustainable dining choices and responsible tableware usage.',
                'body' => 'NatureOne brings premium, food-safe, and compostable tableware to households and businesses. This article covers practical ways to adopt eco-friendly habits without compromising quality or convenience.',
                'excerpt' => 'Practical ways to adopt eco-friendly tableware without sacrificing quality.',
                'image' => '/upload/blogs/blog-' . (($index % 5) + 1) . '.jpg',
                'featured_image' => '/upload/blogs/blog-' . (($index % 5) + 1) . '.jpg',
                'author' => 'NatureOne Editorial',
                'category' => $row['category'],
                'status' => true,
                'is_published' => true,
                'is_featured' => $index === 0,
                'published_at' => $publishDate,
                'publish_date' => $publishDate,
                'created_at' => $publishDate,
                'updated_at' => $publishDate,
            ]);
        }
    }
}

