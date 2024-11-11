<?php


namespace Database\Seeders;

use App\Models\Article;
use Illuminate\Database\Seeder;
use App\Models\Source;

class ArticleSeeder extends Seeder {
    public function run(): void {
        Source::factory()->count(3)->create()->each(function ($source) {
            Article::factory()->count(10)->create(['source_id' => $source->id]);
        });
    }
}
