<?php


namespace Database\Factories;

use App\Models\Source;
use Illuminate\Database\Eloquent\Factories\Factory;

class SourceFactory extends Factory
{
    protected $model = Source::class;

    public function definition()
    {
        return [
            'name' => $this->faker->unique()->company,
            'api_url' => $this->faker->url,
            'api_key' => $this->faker->uuid,
            'metadata' => json_encode(['region' => $this->faker->country]),
        ];
    }
}
