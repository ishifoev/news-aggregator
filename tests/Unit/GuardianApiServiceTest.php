<?php

namespace Tests\Unit;

use App\Services\GuardianApiService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GuardianApiServiceTest extends TestCase
{
    public function test_fetch_articles_from_guardian_api()
    {
        Http::fake([
            env('GUARDIAN_API_URL').'*' => Http::response([
                'response' => [
                    'results' => [
                        [
                            'webTitle' => 'Guardian Test Article',
                            'fields' => ['bodyText' => 'Guardian content', 'byline' => 'Guardian Author'],
                            'webUrl' => 'http://example.com/guardian',
                            'sectionName' => 'Guardian News',
                        ],
                    ],
                ],
            ], 200),
        ]);

        $guardianApiService = new GuardianApiService;
        $articles = $guardianApiService->fetchArticles();

        $this->assertNotEmpty($articles);
        $this->assertEquals('Guardian Test Article', $articles[0]['title']);
    }
}
