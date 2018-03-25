<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Ecrawler\SiteCrawler;

use Goutte;
use Ecrawler\Entity\ArticleInfo;

/**
 * Description of EFudosan
 *
 * @author murata_sho
 */
class EFudosan extends BaseSiteCrawler {
    private $baseUrls = [
        'http://www.e-fudousanhanbai.com/category/mansion_result.asp',
        'http://www.e-fudousanhanbai.com/category/apart_result.asp',
        'http://www.e-fudousanhanbai.com/category/buil_result.asp?bunrui=27',
    ];
    private $domainUrl = 'http://www.e-fudousanhanbai.com/';
    private $noPrintingImg = 'pic_now_printing.gif';
    protected $siteId = 5;
    private $logger;
    private $itemXPathDic = [
            "name" => '//th[@abbr="物件名称"]/following-sibling::td[1]/text()',
            "traffic" => '//th[@abbr="交通"]/following-sibling::td[1]/text()',
            "location" => '//table[contains(@class,"details")]//th[@abbr="所在地"]/following-sibling::td[1]/text()',
            "article_item" => '//th[@abbr="物件種別"]/following-sibling::td[1]/text()',
            "price" => '//th[@abbr="販売価格"]/following-sibling::td[1]/text()',
            "administrative_expense" => self::NO_SCRAP_ITEM_XPATH,
            "repair_financial_reserve" => self::NO_SCRAP_ITEM_XPATH,
            "tenancy" => self::NO_SCRAP_ITEM_XPATH,
            "premium" => self::NO_SCRAP_ITEM_XPATH,
            "deposit" => self::NO_SCRAP_ITEM_XPATH,
            "maintenance_cost" => self::NO_SCRAP_ITEM_XPATH,
            "lump_sum" => self::NO_SCRAP_ITEM_XPATH,
            "annual_planned_income" => '//th[@abbr="年間収入"]/following-sibling::td[1]/text()',
            "yield" => '//th[@abbr="満室想定利回り"]/following-sibling::td[1]/text()',
            "layout" => '//th[@abbr="部屋タイプ"]/following-sibling::td[1]/text()',
            "storey" => self::NO_SCRAP_ITEM_XPATH,
            "building_area" => '//th[@abbr="床面積"]/following-sibling::td[1]/text()',
            "land_area" => '//th[@abbr="土地面積"]/following-sibling::td[1]/text()',
            "building_structure" => '//th[@abbr="構造"]/following-sibling::td[1]/text()',
            "time_old" => '//th[@abbr="築年数"]/following-sibling::td[1]/text()',
            "land_right" => '//th[@abbr="土地権利"]/following-sibling::td[1]/text()',
            "city_planning" => '//th[@abbr="法令関連"]/following-sibling::td[1]/text()',
            "restricted_zone" => '//th[@abbr="用途区分"]/following-sibling::td[1]/text()',
            "coverage_ratio" => '//th[@abbr="建ぺい率"]/following-sibling::td[1]/text()',
            "floor_area_ratio" => '//th[@abbr="容積率"]/following-sibling::td[1]/text()',
            "parking_lot" => '//th[@abbr="駐車場"]/following-sibling::td[1]/text()',
            "motorcycle_place" => self::NO_SCRAP_ITEM_XPATH,
            "bicycle_parking_lot" => self::NO_SCRAP_ITEM_XPATH,
            "private_road_burden_area" => self::NO_SCRAP_ITEM_XPATH,
            "contact_with_road" => '//th[@abbr="接道状況"]/following-sibling::td[1]/text()',
            "classification_of_land_and_category" => self::NO_SCRAP_ITEM_XPATH,
            "geographical_features" => self::NO_SCRAP_ITEM_XPATH,
            "the_total_number_of_houses" => '//th[@abbr="戸数"]/following-sibling::td[1]/text()',
            "country_law_report" => self::NO_SCRAP_ITEM_XPATH,
            "reform_and_renovation_important_notice" => self::NO_SCRAP_ITEM_XPATH,
            "special_report" => self::NO_SCRAP_ITEM_XPATH,
            "facilities" => '//th[@abbr="設備"]/following-sibling::td[1]/text()',
            "remarks" => '//th[@abbr="おすすめ"]/following-sibling::td[1]/text()',
            "conditions" => self::NO_SCRAP_ITEM_XPATH,
            "present_condition" => '//th[@abbr="入居状況"]/following-sibling::td[1]/text()',
            "delivery" => self::NO_SCRAP_ITEM_XPATH,
            "article_number" => '//th[@abbr="物件番号"]/following-sibling::td[1]/text()',
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
    private $itemImageXPathDic = [
        "image_url1" => '//th[@abbr="物件写真"]/following-sibling::td[1]//img/@src',
    ];
    private $is404XPath = '//th[@abbr="物件番号"]/following-sibling::td[1]';
    
    // コンストラクタ
    function __construct() {
        preg_match("/[^\\\\]+$/is", get_class($this), $retArr);
        $className = $retArr[0];
        parent::__construct($className);
        $this->logger = parent::getLogger();
        
        $this->itemXPathDic = array_merge($this->itemXPathDicMaster, $this->itemXPathDic);
    }
    
    /**
     * URLリスト取得
     * @return type
     */
    public function getUrlList() {
        foreach($this->baseUrls as $url) {
            $this->getDetailListUrl($url);        
        }
    }
    
    private function getDetailListUrl($detailUrl) {
        $client = MyClient::getPreparedClient();  
        $crawler = $client->request('GET', $detailUrl);
        // PRエリアを除くため、親のdivから特定
        $detailTargetSelector = '//a[text()="詳細"]/@href'; // 詳細ページURL

        // 詳細ページ保存
        $detailUrls = $crawler->filterXPath($detailTargetSelector)->each(function ($node) {
            $detailUrl = $node->text();
            $this->logger->addInfo("詳細ページ：{$detailUrl}");
            return $detailUrl;
        }); 
        foreach ($detailUrls as $url) {
            $this->saveUrlQuere($url);
            if (!$this->isShouldGoNext()) {
                return;
            }
        }

        // 次ページ取得
        // TODO 何故か次のページが取れない
        $nextTargetSelector = '//a[contains(text(),"次の500件")]/@href';
        $nextUrl = $crawler->filterXPath($nextTargetSelector)->each(function ($node) {
            $tempUrl = $this->domainUrl . $node->text();
            $this->logger->addInfo("次ページ：{$tempUrl}");
            return $tempUrl;
        }); 
        if (count($nextUrl) > 0) {
            // 1秒遅延
            usleep(self::SLEEP_MICRO_SECOND);
            $this->getDetailListUrl($nextUrl[0]);
        }
    }
    
    /**
     * 物件情報抽出処理
     * 
     * @param type $url
     * @param type $crawler
     * @return ArticleInfo
     */
    protected function scrapArticleContentWithXPathDic($url, $crawler) {
        $article = new ArticleInfo();
        $article->set("site", $this->siteId);
        $article->set("url", $url);
        
        foreach ($this->itemXPathDic as $key => $xpath) {
            if ($xpath == self::NO_SCRAP_ITEM_XPATH) {
                $article->set($key, self::NO_SCRAP_ITEM_XPATH);
            } else {
                $dom = $crawler->filterXPath($xpath);
                if ($dom->count()) {
                    $article->set($key, trim($dom->text()));
                } else {
                    $article->set($key, self::NO_SCRAP_ITEM_XPATH);
                }
            }
        }
        return $article;
    }
    
    /**
     * 物件画像抽出処理
     * 
     * @param type $crawler
     * @return type
     */
    protected function scrapImagesContentWithXPathDic($crawler) {
        // URL例
        // http://image.homes.jp/smallimg/image.php?file=%2Fdata%2F0102554%2Fsale%2Fimage%2F0000202-1-1.JPG&width=567&height=372
        // 画像URL
        $images = [];
        foreach ($this->itemImageXPathDic as $key => $xpath) {
            if ($xpath == self::NO_SCRAP_ITEM_XPATH) {
                //$images[$key] = self::NO_SCRAP_ITEM_XPATH;
                // 何もしない
            } else {
                $urls = $crawler->filterXPath($xpath)->extract('_text');
                foreach ($urls as $text) {
                    if (!empty($text) && strpos($text, $this->noPrintingImg) === false) {
                        $images[] = trim($text);
                    }
                } 
            }
        }
        return $images;
    }
    
    /**
     * 物件情報オブジェクトから検索用物件情報オブジェクトを生成して返す
     * ※現在はプロシージャで行っているため廃止
     * @param ArticleInfo $article
     * @return SearchArticleInfo
     */
    public function getSearchArticleInfo(ArticleInfo $article){
//        $articleId      = $article->get("id");
//        $prefectureId   = SearchArticle::getPrefectureId(
//                SearchArticle::getPrefecture($article->get("location")));
//        $articleTypeId  = SearchArticle::getArticleTypeId($article->get("article_item"));
//        if (is_null($articleTypeId)) { $articleTypeId = SearchArticle::getArticleTypeId("区分マンション"); }
//        $yield          = SearchArticle::parseNumeric($article->get("yield"));
//        $price          = SearchArticle::parseNumeric($article->get("price"));
//        $pubDate        = SearchArticle::parseJapaneseDate($article->get("information_pub_date"));
//        return new SearchArticleInfo([
//            "article_id"        => $articleId,
//            "prefecture_id"     => $prefectureId,
//            "article_type_id"   => $articleTypeId,
//            "yield"             => $yield,
//            "price"             => $price,
//            "pub_date"          => $pubDate,
//        ]);
    }

    protected function getDeleteCheckCrawledArticleGenerator($model) {
        foreach ($model->getDeleteCheckArticleGenerator($this->siteId) as $row) {
            yield $row;
        }
    }
    
    protected function is404($crawler, $client) {
        // 物件番号が取れなければ404
        $dom = $crawler->filterXPath($this->is404XPath);
        return ($dom->count())? false : true;
    }
}
