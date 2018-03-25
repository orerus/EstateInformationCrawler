<?php
namespace Ecrawler\SiteCrawler;
require_once(dirname(__FILE__) . '/../../vendor/autoload.php');
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Cache_Lite;
use Goutte;
use Environment;
use Ecrawler\Model\Article;
use Ecrawler\Model\ArticleImage;
use Ecrawler\Model\SearchArticle;
use Ecrawler\Model\UrlQueue AS QueueModel;
use Ecrawler\Entity\UrlQueue AS Queue;
use Ecrawler\Entity\ArticleInfo;

abstract class BaseSiteCrawler implements SiteCrawler {
    protected $cache;
    protected $logging_dir = '/www/sns/batch/log/';
    private $className;
    private $logger;
    private $pdo;
    private $currentCrawlCount;
    const MAX_CRAWL_COUNT = -1; //-1 で全件取得。あとでパラメータで受け取れるようにする
    const SLEEP_MICRO_SECOND = 2000000; // 1000000で1秒
    const POP_QUEUES = 10;
    protected $itemXPathDicMaster = [
        "name" => self::NO_SCRAP_ITEM_XPATH,
        "traffic" => self::NO_SCRAP_ITEM_XPATH,
        "location" => self::NO_SCRAP_ITEM_XPATH,
        "article_item" => self::NO_SCRAP_ITEM_XPATH,
        "price" => self::NO_SCRAP_ITEM_XPATH,
        "administrative_expense" => self::NO_SCRAP_ITEM_XPATH,
        "repair_financial_reserve" => self::NO_SCRAP_ITEM_XPATH,
        "tenancy" => self::NO_SCRAP_ITEM_XPATH,
        "premium" => self::NO_SCRAP_ITEM_XPATH,
        "deposit" => self::NO_SCRAP_ITEM_XPATH,
        "maintenance_cost" => self::NO_SCRAP_ITEM_XPATH,
        "lump_sum" => self::NO_SCRAP_ITEM_XPATH,
        "annual_planned_income" => self::NO_SCRAP_ITEM_XPATH,
        "yield" => self::NO_SCRAP_ITEM_XPATH,
        "layout" => self::NO_SCRAP_ITEM_XPATH,
        "storey" => self::NO_SCRAP_ITEM_XPATH,
        "building_area" => self::NO_SCRAP_ITEM_XPATH,
        "land_area" => self::NO_SCRAP_ITEM_XPATH,
        "building_structure" => self::NO_SCRAP_ITEM_XPATH,
        "time_old" => self::NO_SCRAP_ITEM_XPATH,
        "land_right" => self::NO_SCRAP_ITEM_XPATH,
        "city_planning" => self::NO_SCRAP_ITEM_XPATH,
        "restricted_zone" => self::NO_SCRAP_ITEM_XPATH,
        "coverage_ratio" => self::NO_SCRAP_ITEM_XPATH,
        "floor_area_ratio" => self::NO_SCRAP_ITEM_XPATH,
        "parking_lot" => self::NO_SCRAP_ITEM_XPATH,
        "motorcycle_place" => self::NO_SCRAP_ITEM_XPATH,
        "bicycle_parking_lot" => self::NO_SCRAP_ITEM_XPATH,
        "private_road_burden_area" => self::NO_SCRAP_ITEM_XPATH,
        "contact_with_road" => self::NO_SCRAP_ITEM_XPATH,
        "classification_of_land_and_category" => self::NO_SCRAP_ITEM_XPATH,
        "geographical_features" => self::NO_SCRAP_ITEM_XPATH,
        "the_total_number_of_houses" => self::NO_SCRAP_ITEM_XPATH,
        "country_law_report" => self::NO_SCRAP_ITEM_XPATH,
        "reform_and_renovation_important_notice" => self::NO_SCRAP_ITEM_XPATH,
        "special_report" => self::NO_SCRAP_ITEM_XPATH,
        "facilities" => self::NO_SCRAP_ITEM_XPATH,
        "remarks" => self::NO_SCRAP_ITEM_XPATH,
        "conditions" => self::NO_SCRAP_ITEM_XPATH,
        "present_condition" => self::NO_SCRAP_ITEM_XPATH,
        "delivery" => self::NO_SCRAP_ITEM_XPATH,
        "article_number" => self::NO_SCRAP_ITEM_XPATH,
        "information_pub_date" => self::NO_SCRAP_ITEM_XPATH,
        "next_time_update_due_date" => self::NO_SCRAP_ITEM_XPATH,
        "business_form" => self::NO_SCRAP_ITEM_XPATH,
        "occupied_area" => self::NO_SCRAP_ITEM_XPATH,
        "balcony" => self::NO_SCRAP_ITEM_XPATH,
        "pet" => self::NO_SCRAP_ITEM_XPATH,
        "site_area" => self::NO_SCRAP_ITEM_XPATH,
        "management_form" => self::NO_SCRAP_ITEM_XPATH,
        "cost_per_square_meter" => self::NO_SCRAP_ITEM_XPATH,
        "setback" => self::NO_SCRAP_ITEM_XPATH,
        "management_company" => self::NO_SCRAP_ITEM_XPATH,
        "management_employees" => self::NO_SCRAP_ITEM_XPATH,
        "main_opening" => self::NO_SCRAP_ITEM_XPATH,
        "other_area" => self::NO_SCRAP_ITEM_XPATH,
    ];
    
    /**
     * クロールを続けることが可能かどうか返す
     * 
     * @return boolean
     */
    protected function isShouldGoNext() {
        if (self::MAX_CRAWL_COUNT < 0) {
            return true;
        } else if ($this->currentCrawlCount < self::MAX_CRAWL_COUNT) {
            return true;
        }
        return false;
    }
    
    /**
     * 現在のクロール数をインクリメントする
     */
    protected function addCurrentCount() {
        $this->currentCrawlCount += 1;
    }
    
    function __construct($className, $useDB = true) {
        $this->className = $className;
        $this->cache = new Cache_Lite([
            'cacheDir' => self::CACHE_DIR,
            'lifeTime' => null,// 無限キャッシュ有効
            'automaticCleaningFactor' => 0, 
            'hashedDirectoryLevel'    => 1,
            'hashedDirectoryUmask'    => 02775,
        ]);
        $this->logger = $this->createLogInstance($className);
        $this->addLog("Batch Start.", 'info');
        $this->pdo = ($useDB)? Environment::getPDOInstance() : null;
        $this->currentCrawlCount = 0;
    }
    
    function __destruct() {
        $this->addLog("Batch Finish.", 'info');
    }
    
    public function getLock() {
        if (file_exists($this->lockFileName())) {
            $this->addLog("Batch already running.", 'warning');
            // sentryにも通知
            $GLOBALS["sentryClient"]->captureMessage("Batch already running. LockFileName:%s", [$this->lockFileName()]);
            return false;
        }
        file_put_contents($this->lockFileName(), "running");
        return true;
    }
    
    public function freeLock() {
        if (file_exists($this->lockFileName())) {
            unlink($this->lockFileName());
        } else {
            $this->addLog("Lock file is not found.", 'warning');
        }
    }
    
    public function getDeleteLock() {
        if (file_exists($this->lockDeleteFileName())) {
            $this->addLog("Batch already running.", 'warning');
            return false;
        }
        file_put_contents($this->lockDeleteFileName(), "running");
        return true;
    }
    
    public function freeDeleteLock() {
        if (file_exists($this->lockDeleteFileName())) {
            unlink($this->lockDeleteFileName());
        } else {
            $this->addLog("Lock file is not found.", 'warning');
        }
    }
    
    private function lockFileName() {
        return self::FLAG_DIR.$this->className.".lock";
    }
    
    private function lockDeleteFileName() {
        return self::FLAG_DIR.$this->className."_delete.lock";
    }
    
    /**
     * Logger初期化
     * @param type $className
     * @return Logger
     */
    public function createLogInstance($className) {
        $logger = new Logger($className);
        // 基本的なロギング
        $logger->pushHandler(new StreamHandler($this->logging_dir.$className.".log", Logger::INFO));
        return $logger;
    }
    
    /**
     * PDO取得
     * 
     * @return type
     * @throws Exception
     */
    public function getPDO() {
        if (!$this->pdo) {
            throw new Exception("PDO not created.");
        }
        return $this->pdo;
    }
    
    /**
     * Logger取得
     * 
     * @return type
     * @throws Exception
     */
    public function getLogger() {
        if (!$this->logger) {
            throw new Exception("Please call createLogInstance before getLogger.");
        }
        return $this->logger;
    }
    
    /**
     * Loggerの初期化状態を気にせずログ出力を行うためのメソッド
     * @param type $message
     * @param type $level
     */
    private function addLog($message, $level) {
        if ($this->logger) {
            $logger = $this->logger;
            switch (strtolower($level)) {
                case 'debug':
                    $logger->addDebug($message);
                    break;
                case 'info':
                    $logger->addInfo($message);
                    break;
                case 'notice':
                    $logger->addNotice($message);
                    break;
                case 'warning':
                    $logger->addWarning($message);
                    break;
                case 'error':
                    $logger->addError($message);
                    break;
                case 'critical':
                    $logger->addCritical($message);
                    break;
                case 'alert':
                    $logger->addAlert($message);
                    break;
                case 'emergency':
                    $logger->addEmergency($message);
                    break;
                default:
                    break;
            }
        }
    }
    
    /**
     * siteIdを返す
     * @return type
     */
    public function getSiteId() {
        return $this->siteId;
    }
    
    public function getUrlHash($url) {
        return hash("sha256", $url);
    }
    
    /**
     * 詳細ページ保存
     * 
     * @param \Ecrawler\SiteCrawler\ArticleInfo $article 物件情報
     * @param Array $images 画像URLリスト
     * @return type
     */
    protected function saveArticle(ArticleInfo $article, Array $images) {
        $pdo = $this->getPDO();
        $model = new Article($pdo);
        $queue = new QueueModel($pdo);
        $search = new SearchArticle($pdo);
        $url = $article->get("url");
        if (!Article::isValidUrl($url)) {
            // 無効なURL
            // キャッシュ削除
            $this->cache->remove($this->getUrlHash($url));
            return;
        } 
        try {
            // トランザクション開始
            // 既存があればスキップする
            // 無期限キャッシュをやめるときは既存データがあればUPDATE文に変えるか、
            // 既存データをあらかじめ削除するようにする
            if ($model->isSavedArticle($url)) { 
                // クロール済みデータの削除
                $queue->deleteUrlQuere($url);
                return;
            }
            $pdo->beginTransaction();
            $model->save($article);
            $insertId = $pdo->lastInsertId();
            $article->set("id", $insertId);
            // 画像保存
            $this->saveImages($insertId, $images);
            // 検索用テーブルの保存
            // 検索テーブルへのデータ保存はMySQLプロシージャへ移行するため廃止
//            $articleForSearch = $this->getSearchArticleInfo($article);
//            $search->save($articleForSearch);
            
            // クロール済みデータの削除
            $queue->deleteUrlQuere($url);
            $pdo->commit();
            $this->addLog("物件詳細情報取得完了。ID:".$insertId, 'info') ;
        } catch (\PDOException $ex) {
            $this->addLog('データベース接続失敗、または画像取得失敗。'.$ex->getMessage(), 'error');
            // 再試行できるようにキャッシュ削除
            $this->cache->remove($this->getUrlHash($url));
            $this->addLog(print_r($article), 'error');
            if (isset($pdo)) {
                try {
                    $pdo->rollback();
                    $queue->deleteUrlQuere($url);
                } catch (Exception $e) {
                    //何もしない
                }
            }
            return;
        }
    }
    
    /**
     * 画像保存処理
     * 
     * @param int $id 物件情報ID
     * @param type $imageUrls 画像URLリスト
     */
    private function saveImages($id, $imageUrls) {
        if (is_null($id) || strlen($id) == 0) {
            $this->addLog("取得対象画像なし。id:{$id}", 'info');
            return;
        }
        $model = new ArticleImage($this->getPDO());
        $index = 0;
        foreach ($imageUrls as $url) {
            $index++;
            try {
                if (!$model->saveImage($id, $url, $index)) {
                    $this->addLog("何らかの理由で画像保存に失敗している可能性在り。id:{$id} URL:{$url}", 'error');
                }
            } catch (\ImagickException $ex) {
                $this->addLog("ImagickException occurred. 何らかの理由で画像保存に失敗している可能性在り。id:{$id} URL:{$url} cause:".$ex->getMessage(), 'error');
            } catch (\Exception $ex) {
                $this->addLog("何らかの理由で画像保存に失敗している可能性在り。id:{$id} URL:{$url}", 'error');
            }
        }
    }
    
    /**
     * 検索用物件情報テーブルに格納するSearchArticleInfoを返す
     */
    //abstract public function getSearchArticleInfo(ArticleInfo $article);
    abstract protected function scrapArticleContentWithXPathDic($url, $crawler);
    abstract protected function scrapImagesContentWithXPathDic($crawler);
    
    /**
     * クロール対象URLの保存
     * @param type $url
     * @return type
     */
    protected function saveUrlQuere($url) {
        try {
            $pdo = $this->getPDO();
            // 保存済みURLとかぶっていないかチェック
            // 無限キャッシュをやめる＆再クロールしてUPDATEする場合はこのチェックを外すこと
//            $selectStmt = $pdo->prepare("SELECT COUNT(id) FROM crawled_article_info WHERE url = :url");
//            $selectStmt -> execute(['url' => $url]);
//            $count = $selectStmt -> fetchColumn();
//            if ($count) { 
//                $this->addLog("既にキュー追加済みの為スキップ：{$url}", 'info'); 
//                return; 
//            }
            $queue = new Queue(["url" => $url, "site_id" => $this->getSiteId()]);
            $model = new QueueModel($pdo);
            if (!$model->save($queue)) {
                $this->addLog("何らかの理由により挿入失敗URL：{$url}", 'error');
            }
        } catch (\PDOException $ex) {
            $this->addLog('データベース接続失敗。'.$ex->getMessage(), 'error');
            $this->addLog("挿入失敗URL：{$url}", 'error');
        } finally {
            $this->addCurrentCount();
        }
    }
    
    /**
     * 詳細ページ取得。
     * キャッシュが存在しない場合、クロールしたDomインスタンスを返す
     * キャッシュが存在する場合、クロール済みなのでfalseを返す
     * @param type $detailUrl
     * @return boolean
     */
    protected function getDetailHtmlCrawler($detailUrl) {
        $urlHash = $this->getUrlHash($detailUrl);
        $cache_data=$this->cache->get($urlHash);
        if ($cache_data) {
//            $crawler = $client->request('HEAD', null);
//            $crawler->clear();
//            $crawler->addHtmlContent($cache_data, 'utf-8');
            // 現在は無期限キャッシュの為、キャッシュされた情報は更新させない
            // よってfalseをリターン。今後、キャッシュ済みも更新する場合は
            // 上記crawlerをリターンする。
            unset($cache_data);
            return false;
        } else {
            usleep(self::SLEEP_MICRO_SECOND);
            $client = MyClient::getPreparedClient();  
            $crawler = $client->request('GET', $detailUrl);
            $status = $client->getResponse()->getStatus();
            unset($client);
            if (($status != 200) && ($status != 304)) {
                unset($crawler);
                $this->logger->addError("ERROR getDetailHtml : ".$detailUrl);
                $this->logger->addInfo("memory usage in foreach h:".memory_get_usage().":".memory_get_usage(true));
                // 何故かエラーとなったときに一番メモリ使用量が上がる為、逼迫していたらGCさせる
                $this->gc_collect();
                $this->logger->addInfo("memory usage in foreach h-dash:".memory_get_usage().":".memory_get_usage(true));                return false;
            }
            $this->cache->save($crawler->html(), $urlHash);
        }
        unset($client);
        return $crawler;
    }
    
    private function gc_collect() {
        //手動gcをかけるたびに僅かずつメモリ使用量が上がるので逼迫している場合のみgc使用
        if (memory_get_usage() > 30000000) {
            gc_collect_cycles();
        }
    }
    
    /**
     * 物件詳細ページのクロール
     * @param type $urlList
     */
    public function scrapContent() {
        $pdo = $this->getPDO();
        $queue = new QueueModel($pdo);
        $count = 0;
        do {
            $rows = $queue->popNextQueues($this->getSiteId(), self::POP_QUEUES);
            foreach ($rows as $row) {
                $crawler = $this->getDetailHtmlCrawler($row["url"]);
                if ($crawler === false) {
                    $this->logger->addInfo( "skip scrap : {$row["url"]}");
                    // クロール済みデータの削除
                    $queue->deleteUrlQuere($row["url"]);
                    continue;
                } else {
                    // ここからスクラップ＆DB挿入
                    // TODO try~catchを実装し、Exceptionが投げられたらskipするようにする
                    $articleInfo   = $this->scrapArticleContentWithXPathDic($row["url"], $crawler);
                    $articleImages = $this->scrapImagesContentWithXPathDic($crawler);
                    $this->saveArticle($articleInfo, $articleImages);
                    unset($articleInfo);
                    unset($articleImages);
                }
                $this->logger->addInfo("memory usage in foreach:".memory_get_usage().":".memory_get_usage(true));
                echo "memory usage in foreach:".memory_get_usage().":".memory_get_usage(true)."\n";
                unset($row);
                unset($crawler);
            }
            $this->logger->addInfo("memory usage out foreach:".memory_get_usage().":".memory_get_usage(true));
            echo "memory usage out foreach:".memory_get_usage().":".memory_get_usage(true)."\n";
            $count = $queue->countQueues($this->getSiteId());
            unset($rows);
        } while ($count !== 0);
    }
    
    public function deleteContent() {
        $pdo = $this->getPDO();
        $client = MyClient::getPreparedClient();
        //$client->followRedirects(false); //リダイレクトさせない…はずだがしてしまう
        $article = new Article($pdo);
        $searchArticle = new SearchArticle($pdo);
        $articleForGetUrls = new Article(Environment::getPDOInstance());
        // まず、URLのリストを取得する
        foreach ($this->getDeleteCheckCrawledArticleGenerator($articleForGetUrls) as $row) {
            $this->logger->addInfo("checkURL:".$row['url']);
            // 404チェックしに行く
            usleep(self::SLEEP_MICRO_SECOND);
            $crawler = $client->request('GET', $row['url']);
            
            // 404ならdeleteFlagを立てる
            if ($this->is404($crawler, $client)) {
                $this->logger->addInfo("is404 :".$row['id']. ':' . $row['url']);
                $article->makeDeleteFlag($row['id']);
                $searchArticle->delete($row['id']);
            }
        }
    }
    
    abstract protected function getDeleteCheckCrawledArticleGenerator($model);
    abstract protected function is404($crawler, $client);
}