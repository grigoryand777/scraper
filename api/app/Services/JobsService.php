<?php

namespace App\Services;

use App\Http\Requests\RedisService;
use App\Jobs\ScrapeUrl;

class JobsService
{
    private RedisService $redisService;

    public function __construct(RedisService $redisService) {
        $this->redisService = $redisService;
    }

    public function get(string $id): array {
        $job = $this->redisService->getHashMap("jobs:$id");
        $job['urls'] = $this->getResultsForUrls($id);
        return $job;
    }

    public function create(array $createDTO): array {
        $jobId = uniqid();
        $job = $this->createJob($createDTO, $jobId);
        $jobUrls = $this->createJobUrls($createDTO, $job['id']);
        $urlSelectors = $this->createUrlSelectors($createDTO, $jobUrls);
        $response = $this->createResponseDTO($job, $jobUrls, $urlSelectors, $jobId);
        $this->startScraping($response['urls'], $jobId);
        return $response;
    }

    public function createJob(array $createDTO, string $id): array {
        $job = [
            'name' => $createDTO['name'],
            'status' => 'pending',
            'id' => $id,
        ];

        return $this->redisService->storeHashMap($job, "jobs:$id");
    }

    public function createJobUrls(array $createDTO, string $jobId): array {
        return array_map(function ($url) use ($createDTO, $jobId) {
            return $this->createJobUrl($url, $jobId);
        }, $createDTO['urls']);
    }

    public function createJobUrl(array $urlDTO, string $jobId): array {
        $id = uniqid();

        $jobUrl = [
            'id' => $id,
            'url' => $urlDTO['url'],
            'status' => 'pending',
        ];

        return $this->redisService->storeHashMap(
            $jobUrl,
            "urls:$jobId:$id");
    }

    public function createUrlSelectors(array $createDTO, array $jobUrls): array {
        $urlSelectors = [];
        foreach ($jobUrls as $jobUrl) {
            $createUrlDTO = $this->findCorrespondingUrl($createDTO['urls'], $jobUrl);
            foreach($createUrlDTO['selectors'] as $selector) {
                $urlSelectors [] = $this->createUrlSelector($selector, $jobUrl['id']);
            }
        }
        return $urlSelectors;
    }

    public function createUrlSelector(array $createSelectorDTO, string $jobUrlId): array {
        $id = uniqid();
        return $this->redisService->storeHashMap([
            'name' => $createSelectorDTO['name'],
            'selector' => $createSelectorDTO['selector'],
            'jobUrlId' => $jobUrlId,
        ], "url_selector:{$jobUrlId}:$id");
    }

    public function findCorrespondingUrl(array $createUrlDTO, array $jobUrl): array {
        $matchingUrl = array_filter($createUrlDTO, function ($payloadUrl) use ($jobUrl) {
            return $jobUrl['url'] === $payloadUrl['url'];
        });
        return reset($matchingUrl);
    }

    public function createResponseDTO(
        array $job, array $jobUrls, array $urlSelectors, string $jobId
    ): array {
        return [
            'id' => $jobId,
            'status' => $job['status'],
            'name' => $job['name'],
            'urls' => $this->assignSelectorsToJobUrls($urlSelectors, $jobUrls),
        ];
    }

    public function assignSelectorsToJobUrls(array $selectors, array $jobUrls): array {
        return array_map(function($jobUrl) use ($selectors) {
            return array_merge(
                $jobUrl,
                ['selectors' => $this->findCorrespondingSelectors($selectors, $jobUrl)]
            );
        }, $jobUrls);
    }

    public function findCorrespondingSelectors(array $selectors, array $jobUrl): array {
        return array_filter($selectors, function ($selector) use ($jobUrl) {
            return $selector['jobUrlId'] === $jobUrl['id'];
        });
    }

    public function getResultsForUrls(string $jobId): array {
        $urlKeys = $this->redisService->getKeys("urls:$jobId:*");
        $urlsWithResults = [];
        $urls = $this->redisService->getMany($urlKeys);
        foreach ($urls as $url) {
            $url['results'] = unserialize($this->redisService->get("results:{$jobId}:{$url['id']}"));
            $url['selectors'] = $this->getSelectorsForUrl($url['id']);
            $urlsWithResults [] = $url;
        }
        return $urlsWithResults;
    }

    public function getSelectorsForUrl(string $urlId): array {
        $keys = $this->redisService->getKeys("url_selector:{$urlId}:*");
        $selectors = [];
        foreach ($keys as $key) {
            $selectors [] = $this->redisService->getHashMap($key);
        }
        return $selectors;
    }

    public function startScraping(array $urls, string $jobId): void {
        foreach ($urls as $url) {
            ScrapeUrl::dispatch($url, $jobId);
            dispatch(new ScrapeUrl($url, $jobId));
        }
    }

    public function deleteJob(string | array $keys) {
        $this->redisService->deleteJob($keys);
    }
}
