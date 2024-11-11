<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Source extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'api_url', 'api_key', 'metadata'];

    public function articles(): HasMany
    {
        return $this->hasMany(Article::class);
    }
}
