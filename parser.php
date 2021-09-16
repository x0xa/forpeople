<?php

namespace ForPeople;

include_once 'vendor\autoload.php';

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use simplehtmldom\HtmlDocument;

use ForPeople\Parser\ParserRBC;
use ForPeople\Storage\MySQL;

$sites = [
    'РБК' => [
        'parser' => ParserRBC::class,
        'url' => 'https://rbc.ru'
    ]
];

$storage = new MySQL(mysqlConfig());

$html = new HtmlDocument();
$http = new Client(['verify' => false ]);

foreach ($sites as $siteName => $siteParams) {
    $dbSite = $storage->getSiteByName($siteName);

    if(empty($dbSite))
        $dbSite = $storage->addSite([
            'name' => $siteName,
            'url' => $siteParams['url']
        ]);

    try {
        $parser = new $siteParams['parser']($dbSite, $storage, $html, $http);

        $parser->loadPage($dbSite['url']);
        $parser->parseNewsList();
        $parser->parseNews();
    }
    catch (GuzzleException $e) {
        echo $e->getMessage();
    }
    catch (Exception $e){
        echo $e->getMessage();
    }
}



