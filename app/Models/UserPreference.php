<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPreference extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'preferred_sources', 'preferred_categories', 'preferred_authors'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}