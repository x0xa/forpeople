<?php

namespace ForPeople\Parser;

use GuzzleHttp\Client;
use ForPeople\Helper;
use ForPeople\Storage\IStorage;
use simplehtmldom\HtmlDocument;

class ParserRBC extends AbstractParser {
    public function __construct(array $site, IStorage $storage, HtmlDocument $html, Client $http){
        $this->http = $http;
        $this->site = $site;
        $this->html = $html;
        $this->storage = $storage;
    }

    public function parseNewsList(): bool {
        $htmlNewsList = $this->html->find('.js-news-feed-list', 0);

        foreach($htmlNewsList->find('.news-feed__item') as $item) {
            if(preg_match("#rbc\.ru#ui", $item->href))
                $this->newsList[] = $item->href;
        }

        return true;
    }

    public function parseNews(): bool {
        foreach ($this->newsList as $url) {
            $newsCount = $this->storage->getNewsCountBySite($this->site['id']);

            if ($newsCount < $this->maxNewsCount) {
                echo "parse " . $url . "\n";
                $this->loadPage($url);
                $this->parseNewsPage();
            }
        }

        return true;
    }

    private function parseNewsTitle(): string {
        $newsTitle = $this->html->find('h1.article__header__title-in', 0);

        if(!$newsTitle)
            $newsTitle = $this->html->find('h2.article__title', 0);

        return $newsTitle ? $newsTitle->innertext : '';
    }

    private function parseNewsBody(): string {
        $newsBody = '';
        $newsBlocks = $this->html->find('.article__text p');
        foreach ($newsBlocks as $newsBlock) {
            if(preg_match("#<div|<blockquote#ui", $newsBlock->innertext))
                continue;

            $newsBody .= $newsBlock->outertext . "\n";
        }

        return $newsBody;
    }

    private function parseNewsImage(): string {
        $newsImage = $this->html->find('img.article__main-image__image', 0);
        return $newsImage ? $newsImage->src : '';
    }

    public function parseNewsPage() {
        $newsTitle = $this->parseNewsTitle();

        if ($newsTitle) {
            $newsBody = $this->parseNewsBody();
            $newsImage = $this->parseNewsImage();

            if ($newsBody) {
                $newsHash = Helper::sha_hash($newsBody);

                $createdNews = $this->storage->findNewsByHash($newsHash);

                if (empty($createdNews)) {
                    $createdNews = $this->storage->addNews($this->currentNewsUrl, $newsTitle, $newsBody, $this->site['id']);
                }

                if ($newsImage) {
                    $imageHash = Helper::sha_hash($newsImage);

                    if (empty($this->storage->findImageByHash($imageHash))) {
                        $this->storage->addNewsImage($createdNews['id'], $newsImage);
                    }
                }
            }
        }
    }
}