<?php

namespace App\Contracts;

use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\Article;

interface ArticleServiceInterface
{
    public function getArticles(array $filters): LengthAwarePaginator;
    public function getArticleById(int $id): Article;
}
