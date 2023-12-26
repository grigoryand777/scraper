<?php

namespace App\Http\Requests;

use Illuminate\Support\Facades\Redis;

class RedisService
{
    public function getHashMap(string $key): array {
        return Redis::hgetall($key);
    }

    public function get(string $key): string {
        return Redis::get($key);
    }

    public function getMany(array $keys): array {
        $results = [];
        $findMany = Redis::multi();
        foreach ($keys as $key) {
            $findMany->hgetall($key);
        }

        $responses = $findMany->exec();
        foreach ($keys as $index => $key) {
            $results[$key] = $responses[$index];
        }
        return $results;
    }

    public function deleteMany(array $keys): void {
        $deleteMany = Redis::multi();
        foreach ($keys as $key) {
            $deleteMany->del($key);
        }

        $deleteMany->exec();
    }


    public function getKeys(string $key): array {
        return Redis::keys($key);
    }

    public function scan(string $pattern) {
        $cursor = null;
        $matchingKeys = [];

        do {
            [$cursor, $keys] = Redis::scan($cursor, 'MATCH', $pattern);
            if ($keys) {
                $matchingKeys = array_merge($matchingKeys, $keys);
            }

        } while ($cursor != 0);
        $results = [];
        foreach ($matchingKeys as $key) {
            $results[$key] = Redis::hgetall($key);
        }
        return $results;
    }

    public function storeHashMap(array $dto,string $keyName) {
        Redis::hmset($keyName, $dto);
        return Redis::hgetAll($keyName);
    }

    public function delete(array|string $keys) {
        Redis::del($keys);
    }
    public function deleteByPattern(string $key) {
        $cursor = null;

        do {
            list($cursor, $keys) = Redis::scan($cursor, 'MATCH', $key, 'COUNT', 1000);

            Redis::del($keys);
        } while ($cursor != 0);
    }

    public function deleteJob(string $jobId): void {
        $this->delete($jobId);
        $urlKeys = Redis::keys("urls:{$jobId}:*");
        $urls = $this->getMany($urlKeys);
        $this->deleteByPattern("urls:$jobId:*");
        foreach($urls as $url) {
            $this->deleteByPattern("url_selector:{$url['id']}:*");
        }
    }

}
