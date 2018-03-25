<?php

require_once(dirname(__FILE__) . '/vendor/autoload.php');
global $sentryClient;
$sentryClient= new Raven_Client(Environment::getSentryDSN());
$error_handler = new Raven_ErrorHandler($sentryClient);
$error_handler->registerExceptionHandler();
$error_handler->registerErrorHandler();
$error_handler->registerShutdownFunction();

// Raven_Autoloader::register(); //composer使わない場合のローダー追加


if (!isset($argv[1])) {
    echo "Please input target site name.\n";
    return;
}
switch($argv[1]) {
    case "AtHomeOneHouse":
        $director = new \Ecrawler\CrawlerDirector(new \Ecrawler\SiteCrawler\AtHomeOneHouse());
        break;
    case "AtHomeOneRoom":
        $director = new \Ecrawler\CrawlerDirector(new \Ecrawler\SiteCrawler\AtHomeOneRoom());
        break;
    case "Homes":
        $director = new \Ecrawler\CrawlerDirector(new \Ecrawler\SiteCrawler\Homes());
        break;
    case "Nomucom":
        $director = new \Ecrawler\CrawlerDirector(new \Ecrawler\SiteCrawler\Nomucom());
        break;
    case "EFudosan":
        $director = new \Ecrawler\CrawlerDirector(new \Ecrawler\SiteCrawler\EFudosan());
        break;
    case "Mitsui":
        $director = new \Ecrawler\CrawlerDirector(new \Ecrawler\SiteCrawler\Mitsui());
        break;
    case "Sumitomo":
        $director = new \Ecrawler\CrawlerDirector(new \Ecrawler\SiteCrawler\Sumitomo());
        break;
    default:
        $director = null;
        break;
}
$mode = null;
if (isset($argv[2])) {
    $mode = $argv[2];
}
if ($director) {
    $director->crawl($mode);
} else {
    echo "Invalid site name. Do nothing.\n";    
}

