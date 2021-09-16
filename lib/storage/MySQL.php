<?php

namespace ForPeople\Storage;

use Exception;
use ForPeople\Helper;
use SafeMySQL;

class MySQL implements IStorage {
    private SafeMySQL $db;

    public function __construct(array $config){
        $this->db = new SafeMySQL([
            'host'      => $config['host'],
            'user'      => $config['user'],
            'pass'      => $config['password'],
            'db'        => $config['basename'],
        ]);
    }

    public function getSiteByName(string $siteName): array {
        $result = $this->db->getRow("SELECT id, name, url FROM sites WHERE name = ?s", $siteName);
        return $result ?: [];
    }

    public function addSite(array $site): array {
        try {
            $this->db->query("INSERT INTO sites SET ?u", $site);
        }
        catch (Exception $ex){
            echo $ex->getMessage() . "\n";
            exit;
        }

        return $this->getSiteById($this->db->insertId());
    }

    public function getSiteById($siteId): array {
        $result = $this->db->getRow("SELECT id, name, url FROM sites WHERE id = ?i", $siteId);
        return $result ?: [];
    }

    public function findNewsByHash(int $hash): array {
        $result = $this->db->getRow("SELECT n.*, i.url AS image_url
                                            FROM news n 
                                            LEFT JOIN images i ON i.news_id = n.id
                                            WHERE n.hash = ?i", $hash);
        return $result ?: [];
    }

    public function findImageByHash(int $hash): array {
        $result = $this->db->getRow("SELECT id, url FROM images WHERE hash = ?i", $hash);
        return $result ?: [];
    }

    public function addNews(string $url, string $title, string $body, int $siteId): array {
        try {
            $newsHash = Helper::sha_hash($body);
            $this->db->query("INSERT INTO news(url, title, text, hash, site_id) VALUES(?s, ?s, ?s, ?i, ?i)", $url, $title, $body, $newsHash, $siteId);
        }
        catch (Exception $ex){
            echo $ex->getMessage() . "\n";
            exit;
        }

        return $this->findNewsByHash($newsHash);
    }

    public function addNewsImage($newsId, $imageUrl): array {
        try {
            $imageHash = Helper::sha_hash($imageUrl);
            $this->db->query("INSERT INTO images(url, news_id, hash) VALUES(?s, ?i, ?i)", $imageUrl, $newsId, $imageHash);
        }
        catch (Exception $ex){
            echo $ex->getMessage() . "\n";
            exit;
        }

        return $this->findImageByHash($imageHash);
    }

    public function getNewsCountBySite($siteId): int {
        return $this->db->getOne("SELECT COUNT(id) FROM news WHERE site_id = ?i", $siteId);
    }

    public function getAllNews(): array {
        return $this->db->getAll("SELECT n.*, i.url AS image_url
                                            FROM news n  
                                            LEFT JOIN images i ON i.news_id = n.id");
    }

    public function findNewsById(int $newsId): array {
        $result = $this->db->getRow("SELECT n.*, i.url AS image_url
                                            FROM news n 
                                            LEFT JOIN images i ON i.news_id = n.id
                                            WHERE n.id = ?i", $newsId);
        return $result ?: [];
    }
}