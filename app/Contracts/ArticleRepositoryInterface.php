<?php

namespace App\Contracts;

use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\Article;

interface ArticleRepositoryInterface
{
    public function getArticles(array $filters): LengthAwarePaginator;
    public function findArticleById(int $id): Article;
}
