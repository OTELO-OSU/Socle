<?php

namespace App\Service;

use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class VersionService
{
    private const GITHUB_API =
    'https://api.github.com/repos/OTELO-OSU/Socle/releases/latest';

    public function __construct(
        private HttpClientInterface $client,
        private CacheInterface $cache,
        private string $appVersion,
    ) {}

    public function getVersionInfo(): array
    {
        $latestVersion = $this->cache->get(
            'github_latest_version',
            function (ItemInterface $item) {
                $item->expiresAfter(3600 * 54 * 7); // 1 week

                $response = $this->client->request(
                    'GET',
                    self::GITHUB_API,
                    ['timeout' => 5]
                );

                return $response->toArray()['tag_name'] ?? null;
            }
        );

        return [
            'current' => $this->appVersion,
            'latest' => $latestVersion,
            'updateAvailable' => $latestVersion !== null
                && version_compare(
                    ltrim($latestVersion, 'v'),
                    ltrim($this->appVersion, 'v'),
                    '>'
                ),
        ];
    }
}
