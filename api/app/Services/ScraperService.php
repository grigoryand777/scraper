<?php

namespace App\Services;

use Illuminate\Support\Facades\Redis;
use Goutte;
class ScraperService
{
    public function scrape($jobUrl, $jobId) {
        $this->saveScrapedData(
            $this->getUrlData($jobUrl['url'], $jobUrl['selectors']),
            $jobUrl['id'],
            $jobId,
        );

    }
    protected function getUrlData($url, $selectors) {
        $crawler = Goutte::request('GET', $url);
        $results = [];
        foreach ($selectors as $selector) {
            $crawler->filter($selector['selector'])->each(function ($node) use (&$results, $selector) {
                $results [$selector['name']] [] = $node->text();
            });

        }
        return $results;
    }

    protected function saveScrapedData($results, $jobId, $jobUrlId) {
        Redis::hset("results:{$jobId}:{$jobUrlId}", serialize($results));
    }
}
