<?php

namespace App\Contracts;

use App\Models\Article;
use Illuminate\Pagination\LengthAwarePaginator;

interface ArticleServiceInterface
{
    public function getArticles(array $filters): LengthAwarePaginator;

    public function getArticleById(int $id): Article;
}
