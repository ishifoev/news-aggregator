<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Source;
use Illuminate\Database\Seeder;

class ArticleSeeder extends Seeder
{
    public function run(): void
    {
        Source::factory()->count(3)->create()->each(function ($source) {
            Article::factory()->count(10)->create(['source_id' => $source->id]);
        });
    }
}
