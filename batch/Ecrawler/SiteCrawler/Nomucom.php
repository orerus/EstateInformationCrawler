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
 * Description of Nomucom
 *
 * @author murata_sho
 */
class Nomucom extends BaseSiteCrawler {
    private $baseUrl = 'http://www.nomu.com/pro/all/?area_ids%5B%5D=&price_down=&price_up=&reserved_yield=&new_period=&fw=';
    private $domainUrl = 'http://www.nomu.com';
    private $noPrintingImg = 'pic_now_printing.gif';
    protected $siteId = 4;
    private $logger;
    private $itemXPathDic = [
            "name" => '//*[@id="content_subtitle"]/div[2]/h1/text()',
            "traffic" => '//*[@id="detailsicon_simple_information"]/tr[4]/td[1]/text()',
            "location" => '//*[@id="detailsicon_simple_information"]/tr[3]/td[1]/text()',
            "article_item" => '//*[@id="content_subtitle"]/div[2]/h1/span/text()',
            "price" => '//*[@id="detailsicon_information_area"]/table/tr[3]/td/text()',
            "administrative_expense" => '//*[@id="detailsicon_information_area"]/table/tr[th/div/span[text()="管理費"]]/td[1]/text()',
            "repair_financial_reserve" => '//*[@id="detailsicon_information_area"]/table/tr[th/div/span[text()="修繕積立金"]]/td[2]/text()',
            "tenancy" => self::NO_SCRAP_ITEM_XPATH,
            "premium" => self::NO_SCRAP_ITEM_XPATH,
            "deposit" => self::NO_SCRAP_ITEM_XPATH,
            "maintenance_cost" => self::NO_SCRAP_ITEM_XPATH,
            "lump_sum" => self::NO_SCRAP_ITEM_XPATH,
            "annual_planned_income" => '//*[@id="detailsicon_simple_information"]/tr[1]/td/ul/li[7]/.',
            "yield" => '//*[@id="detailsicon_simple_information"]/tr[1]/td/ul/li[5]/.',
            "layout" => '//*[@id="detailsicon_information_area"]/table/tr[th/div/span[text()="間取り"]]/td/text()',
            "storey" => '//*[@id="detailsicon_information_area"]/table/tr[th/div/span[text()="所在階"]]/td[2]/text()',
            "building_area" => '//*[@id="detailsicon_information_area"]/table/tr[th/div/span[text()="建物面積"]]/td[1]/text()',
            "land_area" => '//*[@id="detailsicon_information_area"]/table/tr[th/div/span[text()="土地面積"]]/td[2]/text()',
            "building_structure" => '//*[@id="detailsicon_information_area"]/table/tr[th/div/span[text()="構造"]]/td[2]/text()',
            "time_old" => '//*[@id="detailsicon_information_area"]/table/tr[th/div/span[text()="築年月"]]/td[1]/text()',
            "land_right" => '//*[@id="detailsicon_information_area"]/table/tr[th/div/span[text()="土地権利"]]/td[1]/text()',
            "city_planning" => '//*[@id="detailsicon_information_area"]/table/tr[th/div/span[text()="都市計画"]]/td[1]/text()',
            "restricted_zone" => '//*[@id="detailsicon_information_area"]/table/tr[th/div/span[text()="用途地域"]]/td[2]/text()',
            "coverage_ratio" => '//*[@id="detailsicon_information_area"]/table/tr[th/div/span[text()="建ぺい率"]]/td[1]/text()',
            "floor_area_ratio" => '//*[@id="detailsicon_information_area"]/table/tr[th/div/span[text()="容積率"]]/td[2]/text()',
            "parking_lot" => '//*[@id="detailsicon_information_area"]/table/tr[th/div/span[text()="駐車場"]]/td/text()',
            "motorcycle_place" => self::NO_SCRAP_ITEM_XPATH,
            "bicycle_parking_lot" => self::NO_SCRAP_ITEM_XPATH,
            "private_road_burden_area" => '//*[@id="detailsicon_information_area"]/table/tr[th/div/span[text()="私道負担"]]/td[1]/text()',
            "contact_with_road" => '//*[@id="detailsicon_information_area"]/table/tr[th/div/span[text()="接道状況"]]/td/text()',
            "classification_of_land_and_category" => '//*[@id="detailsicon_information_area"]/table/tr[th/div/span[text()="地目"]]/td[2]/text()',
            "geographical_features" => self::NO_SCRAP_ITEM_XPATH,
            "the_total_number_of_houses" => '//*[@id="detailsicon_information_area"]/table/tr[th/div/span[text()="総戸数"]]/td[2]/text()',
            "country_law_report" => '//*[@id="detailsicon_information_area"]/table/tr[th/div/span[text()="国土法届出"]]/td[2]/text()',
            "reform_and_renovation_important_notice" => self::NO_SCRAP_ITEM_XPATH,
            "special_report" => self::NO_SCRAP_ITEM_XPATH,
            "facilities" => '//*[@id="detailsicon_information_area"]/table/tr[th/div/span[text()="設備"]]/td/text()',
            "remarks" => '//*[@id="detailsicon_information_area"]/table/tr[th/div/span[text()="備考"]]/td/text()',
            "conditions" => self::NO_SCRAP_ITEM_XPATH,
            "present_condition" => '//*[@id="detailsicon_information_area"]/table/tr[th/div/span[text()="現況"]]/td[1]/text()',
            "delivery" => '//*[@id="detailsicon_information_area"]/table/tr[th/div/span[text()="引渡"]]/td[2]/text()',
            "article_number" => '//*[@id="detailsicon_side_contacts"]/div[2]/div[1]/text()', // 物件番号 XXXXという形
            "information_pub_date" => '//*[@id="detailsicon_information_area"]/table/tr[th/div/span[text()="更新日"]]/td[1]/text()',
            "next_time_update_due_date" => '//*[@id="detailsicon_information_area"]/table/tr[th/div/span[text()="次回更新予定日"]]/td[2]/text()',
            "business_form" => '//*[@id="detailsicon_information_area"]/table/tr[th/div/span[text()="取引態様"]]/td[1]/text()',
            "occupied_area" => '//*[@id="detailsicon_information_area"]/table/tr[th/div/span[text()="専有面積"]]/td/text()',
            "balcony" => '//*[@id="detailsicon_information_area"]/table/tr[th/div/span[text()="バルコニー面積"]]/td[1]/text()',
            "pet" => self::NO_SCRAP_ITEM_XPATH,
            "site_area" => self::NO_SCRAP_ITEM_XPATH,
            "management_form" => '//*[@id="detailsicon_information_area"]/table/tr[th/div/span[text()="管理形態"]]/td[1]/text()',
            "cost_per_square_meter" => self::NO_SCRAP_ITEM_XPATH,
            "setback" => '//*[@id="detailsicon_information_area"]/table/tr[th/div/span[text()="セットバック"]]/td[2]/text()',
            "management_company" => '//*[@id="detailsicon_information_area"]/table/tr[th/div/span[text()="管理会社"]]/td[1]/text()',
            "management_employees" => '//*[@id="detailsicon_information_area"]/table/tr[th/div/span[text()="管理員"]]/td[1]/text()',
            "main_opening" => '//*[@id="detailsicon_information_area"]/table/tr[th/div/span[text()="主開口部"]]/td[2]/text()',
            "other_area" => '//*[@id="detailsicon_information_area"]/table/tr[th/div/span[text()="その他面積"]]/td/text()',
        ];
    private $itemImageXPathDic = [
        "image_url1" => '//*[@id="photo_mainimg"]/img/@src',
        "image_url2" => '//*[@id="mainphotolist"]/li/a/img/@src',
        "image_url3" => '//*[@id="layout_first_image"]/@src',
        "image_url4" => '//*[@id="layout_first_image"]/@to_self',
        "image_url5" => '//*[@id="layout_first_image"]/@to_main',
    ];
    private $is404XPath = '//*[@id="content_subtitle"]/h1';
    
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
        $this->getDetailListUrl($this->baseUrl);
    }
    
    private function getDetailListUrl($detailUrl) {
        $client = MyClient::getPreparedClient();  
        $crawler = $client->request('GET', $detailUrl);
        // PRエリアを除くため、親のdivから特定
        $detailTargetSelector = '//*[@id="search_list"]/li/table/tr[1]/td[2]/h3/a/@href'; // 詳細ページURL

        // 詳細ページ保存
        $detailUrls = $crawler->filterXPath($detailTargetSelector)->each(function ($node) {
            $detailUrl = $this->domainUrl
                   . $node->text();
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
        $nextTargetSelector = '//div[contains(@class, "block_section_1_1")][not(contains(@class, "bottom"))]//span[contains(@class, "page_next-btn")]/a/@href';
        $nextUrl = $crawler->filterXPath($nextTargetSelector)->each(function ($node) {
            $tempUrl = $this->domainUrl . $node->text();
            $this->logger->addInfo("次ページ：{$tempUrl}");
            return $tempUrl;
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
                    $text = trim($dom->text());
                    switch($key) {
                        case "coverage_ratio":
                            $text = $this->getCoverageRatio($text);
                            break;
                        case "floor_area_ratio":
                            $text = $this->getFloorAreaRatio($text);
                            break;
                        case "information_pub_date":
                            $text = $this->getInformationPubDate($text);
                            break;
                        case "next_time_update_due_date":
                            $text = $this->getNextTimeUpdateDueDate($text);
                            break;
                        case "business_form":
                            $text = $this->getBusinessForm($text);
                            break;
                        default:
                            break;
                    }
                    $article->set($key, $text);
                } else {
                    $article->set($key, self::NO_SCRAP_ITEM_XPATH);
                }
            }
        }
        return $article;
    }
    
    /**
     * 建ぺい率の文言取得
     * 建ぺい率／容積率の文字列から建ぺい率のみ取得
     * 
     * @param type $mixed
     * @return type
     */
    private function getCoverageRatio($mixed) {
        // 60%／200%こんな感じで入ってくる
        $array = explode('／', $mixed);
        if (count($array) === 2) {
            return $array[0];
        }
        return self::NO_SCRAP_ITEM_XPATH;
    }
    
    /**
     * 容積率の文言取得
     * 建ぺい率／容積率の文字列から容積率のみ取得
     * 
     * @param type $mixed
     * @return type
     */
    private function getFloorAreaRatio($mixed) {
        // 60%／200%こんな感じで入ってくる
        $array = explode('／', $mixed);
        if (count($array) === 2) {
            return $array[1];
        }
        return self::NO_SCRAP_ITEM_XPATH;
    }
    
    /**
     * 情報公開日の取得
     * @param type $subject
     * @return type
     */
    private function getInformationPubDate($subject) {
        // 情報登録日：2010/07/09 こんな感じで入ってくる
        return str_replace('情報登録日：', '', $subject);
    }
    
    /**
     * 次回更新日の取得
     * @param type $subject
     * @return type
     */
    private function getNextTimeUpdateDueDate($subject) {
        // 有効期限：2016/10/01 こんな感じで入ってくる
        return str_replace('有効期限：', '', $subject);
    }
    
    /**
     * 取引形態の取得
     * @param type $subject
     * @return type
     */
    private function getBusinessForm($subject) {
        //  : 仲介こんな感じで入ってくる
        return trim(str_replace(':', '', $subject));
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
                        // 拡大時の画像サイズを使用
                        $images[] = preg_replace('/_[0-9]{2}.jpg/', '_25.jpg', trim($text));
                    }
                } 
            }
        }
        $domain = $this->domainUrl;
        return array_map(function($path) use ($domain){return $domain.$path;}, array_unique(array_filter($images, "strlen")));
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
        return ($crawler->filterXPath($this->is404XPath)->each(function ($node) {
            return ($node->text() == '物件の掲載が終了しました');
        }))? true : false;
    }
}
