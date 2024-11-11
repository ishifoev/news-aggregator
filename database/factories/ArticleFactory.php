<?php

namespace Database\Factories;

use App\Models\Article;
use App\Models\Source;
use Illuminate\Database\Eloquent\Factories\Factory;

class ArticleFactory extends Factory
{
    protected $model = Article::class;

    public function definition()
    {
        return [
            'title' => $this->faker->sentence,
            'content' => $this->faker->paragraphs(3, true),
            'author' => $this->faker->name,
            'source_id' => Source::factory(),
            'category' => $this->faker->word,
            'published_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ];
    }
}
