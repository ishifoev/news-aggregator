<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserPreference;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserPreferenceSeeder extends Seeder
{
    public function run()
    {
        DB::transaction(function () {
            User::chunk(100, function ($users) {
                foreach ($users as $user) {
                    UserPreference::updateOrCreate(
                        ['user_id' => $user->id],
                        [
                            'preferred_sources' => json_encode(['TechCrunch', 'BBC']),
                            'preferred_categories' => json_encode(['Technology', 'Science']),
                            'preferred_authors' => json_encode(['John Doe']),
                        ]
                    );
                }
            });
        });
    }
}
