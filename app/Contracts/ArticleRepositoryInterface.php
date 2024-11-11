<?php

namespace App\Contracts;

use App\Models\Article;
use Illuminate\Pagination\LengthAwarePaginator;

interface ArticleRepositoryInterface
{
    public function getArticles(array $filters): LengthAwarePaginator;

    public function findArticleById(int $id): Article;
}
