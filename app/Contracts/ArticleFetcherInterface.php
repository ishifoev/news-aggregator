<?php

namespace App\Contracts;

interface ArticleFetcherInterface
{
    public function fetchArticles(): array;
}
