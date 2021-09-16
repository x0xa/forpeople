<?php

namespace ForPeople\Storage;

interface IStorage {
    public function getSiteByName(string $siteName): array;
    public function addSite(array $site): array;
    public function getSiteById($siteId): array;
    public function findNewsByHash(int $hash): array;
    public function findNewsById(int $newsId): array;
    public function findImageByHash(int $hash): array;
    public function addNews(string $url, string $title, string $body, int $siteId): array;
    public function addNewsImage($newsId, $imageUrl): array;
    public function getNewsCountBySite($siteId): int;
    public function getAllNews(): array;
}