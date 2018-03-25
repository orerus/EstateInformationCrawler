<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Ecrawler\SiteCrawler;


use Goutte;
use Ecrawler\Entity\ArticleInfo;
use Ecrawler\Entity\SearchArticleInfo;
use Ecrawler\Model\SearchArticle;

/**
 * Description of AtHomeOneRoom
 *
 * @author murata_sho
 */
class AtHomeOneRoom extends BaseSiteCrawler {
    private $baseUrl = 'http://toushi-athome.jp/ei_41/';
    private $domainUrl = 'http://toushi-athome.jp';
    private $noPrintingImg = 'pic_now_printing.gif';
    protected $siteId = 2;
    private $logger;
    private $itemXPathDic = [
            "name" => '//tr[th[contains(text(), "建物名") and contains(text(), "部屋番号")]]/td[1]/text()',
            "traffic" => '//tr[th[contains(text(), "交通")]]/td/p/text()',
            "location" => '//tr[th[text()="所在地"]]/td/text()',
            "article_item" => '//tr[th[text()="物件種目"]]/td/text()',
            "price" => '//tr[th[text()="価格"]]/td[1]/text()',
            "administrative_expense" => '//tr[th[text()="管理費等"]]/td[1]/text()',
            "repair_financial_reserve" => '//tr[th[text()="修繕積立金"]]/td[2]/text()',
            "tenancy" => '//tr[th[contains(text(),"借地期間")]]/td[1]/text()',
            "premium" => '//tr[th[text()="権利金"]]/td[2]/text()',
            "deposit" => '//tr[th[text()="敷金または保証金"]]/td[1]/text()',
            "maintenance_cost" => '//tr[th[text()="維持費等"]]/td[2]/text()',
            "lump_sum" => '//tr[th[text()="その他一時金"]]/td[1]/text()',
            "annual_planned_income" => '//tr[th[text()="年間予定収入"]]/td[1]/text()',
            "yield" => '//tr[th[text()="利回り"]]/td[2]/text()',
            "layout" => '//tr[th[text()="間取り"]]/td[1]/text()',
            "storey" => '//tr[th[text()="階建 / 階"]]/td[1]/text()',
            "building_area" => self::NO_SCRAP_ITEM_XPATH,
            "land_area" => self::NO_SCRAP_ITEM_XPATH,
            "building_structure" => '//tr[th[text()="建物構造"]]/td[2]/text()',
            "time_old" => '//tr[th[text()="築年月"]]/td[1]/text()',
            "land_right" => '//tr[th[text()="土地権利"]]/td[1]/text()',
            "city_planning" => self::NO_SCRAP_ITEM_XPATH,
            "restricted_zone" => self::NO_SCRAP_ITEM_XPATH,
            "coverage_ratio" => self::NO_SCRAP_ITEM_XPATH,
            "floor_area_ratio" => self::NO_SCRAP_ITEM_XPATH,
            "parking_lot" => '//tr[th[text()="駐車場"]]/td[1]/text()',
            "motorcycle_place" => '//tr[th[text()="バイク置き場"]]/td[2]/text()',
            "bicycle_parking_lot" => '//tr[th[text()="駐輪場"]]/td[1]/text()',
            "private_road_burden_area" => self::NO_SCRAP_ITEM_XPATH,
            "contact_with_road" => self::NO_SCRAP_ITEM_XPATH,
            "classification_of_land_and_category" => self::NO_SCRAP_ITEM_XPATH,
            "geographical_features" => self::NO_SCRAP_ITEM_XPATH,
            "the_total_number_of_houses" => '//tr[th[text()="総戸数"]]/td[2]/text()',
            "country_law_report" => '//tr[th[text()="国土法届出"]]/td[2]/text()',
            "reform_and_renovation_important_notice" => '//tr[th[text()="リフォーム / リノベーション"]]/td[1]/text()',
            "special_report" => '//tr[th[text()="特記事項"]]/td[1]/text()', //サイトになかった
            "facilities" => '//tr[th[text()="設備"]]/td[1]/text()',
            "remarks" => '//tr[th[text()="備考"]]/td[1]/text()',
            "conditions" => '//tr[th[text()="条件等"]]/td[1]/text()',
            "present_condition" => '//tr[th[text()="現況"]]/td[2]/text()',
            "delivery" => '//tr[th[text()="引渡し"]]/td[1]/text()',
            "article_number" => '//tr[th[text()="物件番号"]]/td[2]/text()',
            "information_pub_date" => '//tr[th[text()="情報公開日"]]/td[1]/text()',
            "next_time_update_due_date" => '//tr[th[text()="次回更新予定日"]]/td[2]/text()',
            "business_form" => self::NO_SCRAP_ITEM_XPATH,
            "occupied_area" => '//tr[th[text()="専有面積"]]/td[2]/text()',
            "balcony" => '//tr[th[text()="バルコニー"]]/td[2]/text()',
            "pet" => '//tr[th[text()="ペット"]]/td[1]/text()',
            "site_area" => '//tr[th[text()="敷地面積"]]/td[2]/text()',
            "management_form" => '//tr[th[text()="管理形態・方式"]]/td[1]/text()',
            "cost_per_square_meter" => '//tr[th[text()="平米単価"]]/td[2]/text()',
            
        ];
    private $itemImageXPathDic = [
        "image_url1" => '//td[contains(@class,"roomA")][1]//img[1]/@src',
        "image_url2" => '//td[contains(@class,"roomA")][2]//img[1]/@src',
        "image_url3" => '//*[starts-with(@id, "image")]/@src',
    ];
    
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
        $client = MyClient::getPreparedClient();
        $crawler = $client->request('GET', $this->baseUrl);
        $targetSelector = '//ul[@class="floLinkList"]/li/a[starts-with(@href,"/ei_41/1")]/@href'; // 地域別
        $crawler->filterXPath($targetSelector)->each(function ($node) {
            if (!$this->isShouldGoNext()) {
                return;
            }
            $prefectureListUrl = $this->domainUrl . $node->text();
            $this->logger->addInfo("県別リストURL：{$prefectureListUrl}");
            // 1秒遅延
            usleep(self::SLEEP_MICRO_SECOND);
            $this->getRegionListUrl($prefectureListUrl);
        });
    }
    
    /**
     * URLリスト取得（地域別一覧ページ）
     * @param type $prefectureListUrl
     */
    private function getRegionListUrl($prefectureListUrl) {
        $client = MyClient::getPreparedClient();
        $crawler = $client->request('GET', $prefectureListUrl);
        $targetSelector = '//ul[@class="floList widthM2"]/li/a[not(@class="out")]/@href'; // 地域別
        $crawler->filterXPath($targetSelector)->each(function ($node) {
            if (!$this->isShouldGoNext()) {
                return;
            }
            $regionListUrl = $this->domainUrl . $node->text();
            $this->logger->addInfo("地域別リストURL（0件除く）：{$regionListUrl}");
            // 1秒遅延
            usleep(self::SLEEP_MICRO_SECOND);
            $this->getDetailListUrl($regionListUrl);
        });    
    }
    
    /**
     * URLリスト取得（詳細ページURL一覧ページ）
     * @param type $detailUrl
     */
    private function getDetailListUrl($detailUrl) {
        $client = MyClient::getPreparedClient();
        $crawler = $client->request('GET', $detailUrl);
        $detailTargetSelector = '//p[@class="station"]/a/@href'; // 詳細ページURL
        
        // 詳細ページ保存
        $detailUrls = $crawler->filterXPath($detailTargetSelector)->each(function ($node) {
            $detailUrl = explode('?', $this->domainUrl
                   . $node->text())[0];
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
        $nextTargetSelector = '(//li[@class="next"]/a/@href)[1]';
        $nextUrl = $crawler->filterXPath($nextTargetSelector)->each(function ($node) {
            $nextUrl = $this->domainUrl . $node->text();
            $this->logger->addInfo("次ページ：{$nextUrl}");
            return $nextUrl;
        }); 
        if (count($nextUrl) === 1) {
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
                        $images[] = explode('?', trim($text))[0];
                    }
                } 
            }
        }
        return $images;
    }
    
    /**
     * 物件情報オブジェクトから検索用物件情報オブジェクトを生成して返す
     * 
     * @param ArticleInfo $article
     * @return SearchArticleInfo
     */
    public function getSearchArticleInfo(ArticleInfo $article){
        $articleId      = $article->get("id");
        $prefectureId   = SearchArticle::getPrefectureId(
                SearchArticle::getPrefecture($article->get("location")));
        $articleTypeId  = SearchArticle::getArticleTypeId($article->get("article_item"));
        if (is_null($articleTypeId)) { $articleTypeId = SearchArticle::getArticleTypeId("区分マンション"); }
        $yield          = SearchArticle::parseNumeric($article->get("yield"));
        $price          = SearchArticle::parseNumeric($article->get("price"));
        $pubDate        = SearchArticle::parseJapaneseDate($article->get("information_pub_date"));
        return new SearchArticleInfo([
            "article_id"        => $articleId,
            "prefecture_id"     => $prefectureId,
            "article_type_id"   => $articleTypeId,
            "yield"             => $yield,
            "price"             => $price,
            "pub_date"          => $pubDate,
        ]);
    }
    
    protected function getDeleteCheckCrawledArticleGenerator($model) {
        foreach ($model->getDeleteCheckArticleGenerator($this->siteId) as $row) {
            yield $row;
        }
    }
    
    protected function is404($crawler, $client) {
        $status = $client->getResponse()->getStatus();
        // リダイレクト先のURLとも突合せを行いたかったが、Goutteの仕様かURLがリダイレクト前のものしか取れなかったので断念
        // ステータスコードだけで判定する
        return ($status != 200)? true : false;
    }
}
