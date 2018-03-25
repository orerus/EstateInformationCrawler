<?php
namespace Ecrawler;
require_once(dirname(__FILE__) . '/../vendor/autoload.php');

class CrawlerDirector {
    // サイトごとのクローラ
    private $siteCrawler;
    
    function __construct($crawlerDef) { 
        $this->siteCrawler = $crawlerDef; 
    } 
    
    // メソッドの宣言
    public function crawl($mode = null) {
        // 404チェックの場合
        if ($mode == 'delete') {
            // 多重起動チェック
            if (!$this->siteCrawler->getDeleteLock()) {
                return;
            }
            try {
                // 404チェック
                $this->siteCrawler->deleteContent();
            } catch (\Exception $ex) {
                throw $ex;
            } finally {
                // 多重起動チェックフラグ削除
                $this->siteCrawler->freeDeleteLock();
            }
            return;
        }

        // 通常クロールの場合
        // 多重起動チェック
        if (!$this->siteCrawler->getLock()) {
            return;
        }
        
        try {
            if (is_null($mode) || $mode == 'queue') {
                // URLリストの取得
                $this->siteCrawler->getUrlList();
            }

            if (is_null($mode) || $mode == 'scrap') {
                // サイトの保存
                $this->siteCrawler->scrapContent();
            }
        } catch (\Exception $ex) {
            throw $ex;
        } finally {
            // 多重起動チェックフラグ削除
            $this->siteCrawler->freeLock();
        }
    }
}