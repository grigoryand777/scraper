<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Redis;
use Goutte;
class ScrapeUrl implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var mixed
     */
    /**
     * @var mixed
     */
    private $jobUrl;
    /**
     * @var mixed
     */
    private $jobId;

    /**
     * Create a new job instance.
     */
    public function __construct($jobUrl, $jobId)
    {
        $this->jobUrl = $jobUrl;
        $this->jobId = $jobId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->setStatus('working');
        $this->saveScrapedData($this->getUrlData($this->jobUrl['url'], $this->jobUrl['selectors']));
        $this->setStatus('completed');
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

    protected function saveScrapedData($results) {
        Redis::set("results:{$this->jobId}:{$this->jobUrl['id']}", serialize($results));
    }

    protected function setStatus($status) {
        Redis::hmset("jobs:{$this->jobId}", 'status', $status);
    }
}
