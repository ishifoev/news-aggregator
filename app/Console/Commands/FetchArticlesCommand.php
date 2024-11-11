<?php

namespace App\Console\Commands;

use App\Repositories\ArticleRepository;
use App\Services\GuardianApiService;
use App\Services\NewsApiService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class FetchArticlesCommand extends Command
{
    protected $signature = 'articles:fetch';

    protected $description = 'Fetch articles from different news sources';

    protected ArticleRepository $articleRepository;

    protected array $fetchers;

    public function __construct(ArticleRepository $articleRepository)
    {
        parent::__construct();
        $this->articleRepository = $articleRepository;
        $this->fetchers = [
            app(NewsApiService::class),
            app(GuardianApiService::class),
        ];
    }

    public function handle()
    {
        foreach ($this->fetchers as $fetcher) {
            $articles = $fetcher->fetchArticles();
            if (! empty($articles)) {
                $this->articleRepository->saveArticlesWithSources($articles);
            }
        }

        Log::info('Articles fetched and stored successfully.');
        $this->info('Articles fetched and stored successfully.');
    }
}
