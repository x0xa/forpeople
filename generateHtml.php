<?php

namespace Hicks;

use ForPeople\Helper;
use ForPeople\Storage\MySQL;

include_once 'vendor\autoload.php';

$storage = new MySQL(mysqlConfig());

array_map('unlink', glob("./view/*"));

foreach ($storage->getAllNews() as $news) {
    $newsFile = "news_".$news['id'].".html";
    file_put_contents("view/list.html", "<h2>" . $news['title'] . "</h2>", FILE_APPEND);
    file_put_contents("view/list.html", "\n<p>" . Helper::prepareExcerpt($news['text'])."</p>", FILE_APPEND);
    file_put_contents("view/list.html", "\n<p><a href=".$newsFile.">Подробнее</a></p>", FILE_APPEND);

    file_put_contents("view/" . $newsFile, "");

    file_put_contents("view/" . $newsFile, "<h2>" . $news['title'] . "</h2>", FILE_APPEND);
    file_put_contents("view/" . $newsFile, "\n<p>" . $news['text']."</p>", FILE_APPEND);

    if($news['image_url'])
        file_put_contents("view/" . $newsFile, "<img src='" . $news['image_url'] . "'/>", FILE_APPEND);
}