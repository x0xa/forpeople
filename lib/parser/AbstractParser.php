<?php

namespace ForPeople\Parser;

use Error;
use GuzzleHttp\Client;
use simplehtmldom\HtmlDocument;

abstract class AbstractParser {
    protected int $maxNewsCount = 15;
    protected array $newsList;
    protected string $currentNewsUrl;
    protected Client $http;
    protected HtmlDocument $html;
    protected array $site;

    public function loadPage(string $url): bool {
        $this->currentNewsUrl = $url;
        $response = $this->http->request('GET', $this->currentNewsUrl);

        if($response->getStatusCode() === 200){
            $this->html->load($response->getBody());
        }
        else{
            return throw new Error('Error response code ' . $response->getStatusCode());
        }

        return true;
    }

    protected abstract function parseNewsList(): bool;
    protected abstract function parseNews(): bool;
}