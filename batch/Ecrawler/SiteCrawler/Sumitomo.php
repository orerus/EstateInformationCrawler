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
 * Description of Mitsui
 *
 * @author murata_sho
 */
class Sumitomo extends BaseSiteCrawler {
    private $baseUrls = [
        'http://www.stepon.co.jp/search/list/?type=pro2&searchType=area&smk=111111111110&ca=0_001',
    ];
    private $domainUrl = 'http://www.stepon.co.jp';
    private $noPrintingImg = 'pic_now_printing.gif';
    protected $siteId = 7;
    private $logger;
    private $itemXPathDic = [
            "name" => '//*[@id="bukkenNameBlockIcon"]/h2/span[3]/text()',
            "traffic" => '//th[text()="交通"]/following-sibling::td[1]/.', // スペース詰める
            "location" => '//th[text()="所在地"]/following-sibling::td[1]/text()',
            "article_item" => '//dt[text()="物件種別"]/parent::dl/text()',
            "price" => '//th[text()="価格"]/following-sibling::td[1]/text()',
            "administrative_expense" => '//th[text()="管理費(月額)"]/following-sibling::td[1]/text()',
            "repair_financial_reserve" => '//th[text()="修繕積立金(月額)"]/following-sibling::td[1]/text()',
            "tenancy" => self::NO_SCRAP_ITEM_XPATH,
            "premium" => self::NO_SCRAP_ITEM_XPATH,
            "deposit" => self::NO_SCRAP_ITEM_XPATH,
            "maintenance_cost" => self::NO_SCRAP_ITEM_XPATH,
            "lump_sum" => self::NO_SCRAP_ITEM_XPATH,
            "annual_planned_income" => self::NO_SCRAP_ITEM_XPATH,
            "yield" => '//dt[text()="利回り"]/following-sibling::dd[1]/.',
            "layout" => '//th[text()="間取り"]/following-sibling::td[1]/text()',
            "storey" => '//th[contains(text(), "・構造")]/following-sibling::td[1]/text()',
            "building_area" => '//th[text()="建物面積"]/following-sibling::td[1]/text()', // 建物面積
            "land_area" => '//th[text()="土地面積"]/following-sibling::td[1]/text()', // 土地面積
            "building_structure" => '//dt[text()="構造"]/parent::dl/text()',
            "time_old" => '//th[text()="築年月"]/following-sibling::td[1]/text()',
            "land_right" => '//th[text()="土地権利"]/following-sibling::td[1]/text()',
            "city_planning" => self::NO_SCRAP_ITEM_XPATH,
            "restricted_zone" => '//th[text()="地域地区"]/following-sibling::td[1]/text()',
            "coverage_ratio" => '//th[text()="建ぺい率"]/following-sibling::td[1]/text()',
            "floor_area_ratio" => '//th[text()="容積率"]/following-sibling::td[1]/text()',
            "parking_lot" => '//th[text()="駐車場"]/following-sibling::td[1]/text()',
            "motorcycle_place" => self::NO_SCRAP_ITEM_XPATH,
            "bicycle_parking_lot" => self::NO_SCRAP_ITEM_XPATH,
            "private_road_burden_area" => self::NO_SCRAP_ITEM_XPATH,
            "contact_with_road" => '//th[text()="接道状況"]/following-sibling::td[1]/text()',
            "classification_of_land_and_category" => '//th[text()="地目／地勢"]/following-sibling::td[1]/text()',
            "geographical_features" => '//th[text()="地目／地勢"]/following-sibling::td[1]/text()',
            "the_total_number_of_houses" => '//th[text()="総戸数"]/following-sibling::td[1]/text()',
            "country_law_report" => '//th[text()="国土法"]/following-sibling::td[1]/text()',
            "reform_and_renovation_important_notice" => self::NO_SCRAP_ITEM_XPATH,
            "special_report" => self::NO_SCRAP_ITEM_XPATH,
            "facilities" => self::NO_SCRAP_ITEM_XPATH,
            "remarks" => '//th[text()="備考"]/following-sibling::td[1]/.',
            "conditions" => self::NO_SCRAP_ITEM_XPATH,
            "present_condition" => '//th[text()="現況"]/following-sibling::td[1]/text()',
            "delivery" => '//th[text()="引渡時期"]/following-sibling::td[1]/text()',
            "article_number" => self::NO_SCRAP_ITEM_XPATH,
            "information_pub_date" => self::NO_SCRAP_ITEM_XPATH,
            "next_time_update_due_date" => self::NO_SCRAP_ITEM_XPATH,
            "business_form" => '//th[text()="取引態様"]/following-sibling::td[1]/text()',
            "occupied_area" => '//th[text()="専有面積"]/following-sibling::td[1]/text()', // 専有面積
            "balcony" => '//th[text()="バルコニー面積"]/following-sibling::td[1]/text()',
            "pet" => self::NO_SCRAP_ITEM_XPATH,
            "site_area" => self::NO_SCRAP_ITEM_XPATH,
            "management_form" => '//th[contains(text(), "管理方式")]/following-sibling::td[1]/text()',
            "cost_per_square_meter" => self::NO_SCRAP_ITEM_XPATH,
            "setback" => self::NO_SCRAP_ITEM_XPATH,
            "management_company" => '//th[contains(text(), "管理方式")]/following-sibling::td[1]/text()',
            "management_employees" => self::NO_SCRAP_ITEM_XPATH,
            "main_opening" => self::NO_SCRAP_ITEM_XPATH,
            "other_area" => self::NO_SCRAP_ITEM_XPATH,
        ];
    private $itemImageXPathDic = [
        "image_url1" => '//div[@id="infoImageBlock"]/ul/li/img',
    ];
    private $is404XPath = '//th[text()="価格"]/following-sibling::td[1]';
    
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
        $detailTargetSelector = '//p[contains(@class, "link")]/a/@href'; // 詳細ページURL

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
        $nextTargetSelector = 'descendant-or-self::a[text()="次へ"]';
        $nextUrls = $crawler->filterXPath($nextTargetSelector)->each(function ($node) {
            return $node->attr('href');
        });
        if (count($nextUrls) > 0) {
            // 1秒遅延
            usleep(self::SLEEP_MICRO_SECOND);
            $nextFullUrl = $this->domainUrl.$nextUrls[0];
            $this->logger->addInfo("次ページ：{$nextFullUrl}");
            $this->getDetailListUrl($nextFullUrl);
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
                    $texts = $dom->each(function($node) {
                        return $node->text();
                    });
                    $text = implode($texts);
                    switch($key) {
                        case "classification_of_land_and_category":
                            $text = $this->getFrontward($text);
                            break;
                        case "geographical_features":
                            $text = $this->getBackward($text);
                            break;
                        case "management_form":
                            $text = $this->getFrontward($text);
                            break;
                        case "management_company":
                            $text = $this->getBackward($text);
                            break;
                        default:
                            break;
                    }
                    // 連続した空白を詰めてtrimしている
                    $article->set($key, trim(preg_replace('/\s+/', ' ', $text)));
                } else {
                    $article->set($key, self::NO_SCRAP_ITEM_XPATH);
                }
            }
        }
        return $article;
    }
    
    /**
     * @param type $mixed
     * @return type
     */
    private function getBackward($mixed) {
        // 60%／200%こんな感じで入ってくる
        $array = explode('／', $mixed);
        if (count($array) === 2) {
            return $array[1];
        }
        return self::NO_SCRAP_ITEM_XPATH;
    }
    
    /**
     * @param type $mixed
     * @return type
     */
    private function getFrontward($mixed) {
        // 60%／200%こんな感じで入ってくる
        $array = explode('／', $mixed);
        if (count($array) === 2) {
            return $array[0];
        }
        return self::NO_SCRAP_ITEM_XPATH;
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
                $urls = $crawler->filterXPath($xpath)->extract('src');
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
        $status = $client->getResponse()->getStatus();
        
        if ($status != 200) {
            return true;
        }

        // 200で掲載終了パターン
        // 価格が取れなければ404
        $dom = $crawler->filterXPath($this->is404XPath);
        return ($dom->count())? false : true;
    }
}
