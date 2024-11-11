<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\UserPreference;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UserPreference>
 */
class UserPreferenceFactory extends Factory
{
    protected $model = UserPreference::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(), // Ensure a user is created if not provided
            'preferred_sources' => json_encode($this->faker->words(3)), // Random array of sources
            'preferred_categories' => json_encode($this->faker->words(2)), // Random array of categories
            'preferred_authors' => json_encode([$this->faker->name]), // Random author name
        ];
    }
}
