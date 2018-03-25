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
 * Description of Homes
 *
 * @author murata_sho
 */
class Homes extends BaseSiteCrawler {
    private $baseUrl = 'http://toushi.homes.co.jp/%E5%8F%8E%E7%9B%8A%E7%89%A9%E4%BB%B6%E6%A4%9C%E7%B4%A2/';
    private $domainUrl = 'http://toushi.homes.co.jp';
    private $noPrintingImg = 'pic_now_printing.gif';
    protected $siteId = 3;
    private $logger;
    private $itemXPathDic = [
            "name" => '//*[@id="bukkenDetailInfo"]/div[2]/h3/text()',
            "traffic" => '//*[@id="bukkenDetailInfo"]/div[@class="bukkenDetail"]/div[@class="bukkenValueInfo"]/table/tr[th[text()="交通"]]/td/text()',
            "location" => '//*[@id="bukkenDetailInfo"]/div[@class="bukkenDetail"]/div[@class="bukkenValueInfo"]/table/tr[th[text()="住所"]]/td/text()',
            "article_item" => '//*[@id="bukkenDetailInfo"]/div[@class="bukkenDetailHeader"]/p/img/@alt',
            "price" => '//*[@id="bukkenDetailInfo"]/div[@class="bukkenDetail"]/div[@class="bukkenValueInfo"]/table/tr[th[text()="価格"]]/td[1]/strong/text()',
            "administrative_expense" => '//*[@id="prg-bukkenItem"]/table/tr[th[span[text()="管理費等"]]]/td[1]/text()',
            "repair_financial_reserve" => '//*[@id="prg-bukkenItem"]/table/tr[th[span[text()="修繕積立金"]]]/td[2]/text()',
            "tenancy" => self::NO_SCRAP_ITEM_XPATH,
            "premium" => self::NO_SCRAP_ITEM_XPATH,
            "deposit" => self::NO_SCRAP_ITEM_XPATH,
            "maintenance_cost" => self::NO_SCRAP_ITEM_XPATH,
            "lump_sum" => self::NO_SCRAP_ITEM_XPATH,
            "annual_planned_income" => '//*[@id="prg-bukkenItem"]/table/tr[th[span[text()="満室想定年収"]]]/td[1]/text()',
            "yield" => '//*[@id="bukkenDetailInfo"]/div[@class="bukkenDetail"]/div[@class="bukkenValueInfo"]/table/tr[th[text()="利回り"]]/td[@class="bukkenYield"]/strong/text()',
            "layout" => '//*[@id="prg-bukkenItem"]/table/tr[td[contains(@class, "madori")]]/td[contains(@class, "madori")]/text()',
            "storey" => '//*[@id="prg-bukkenItem"]/table/tr[th[span[text()="所在階／階数"]]]/td[2]/text()',
            "building_area" => '//*[@id="prg-bukkenItem"]/table/tr[th[span[text()="建物面積"]]]/td/text()',
            "land_area" => '//*[@id="prg-bukkenItem"]/table/tr[th[span[text()="土地面積"]]]/td/text()',
            "building_structure" => '//*[@id="prg-bukkenItem"]/table/tr[th[span[text()="建物構造"]]]/td[1]/text()',
            "time_old" => '//*[@id="prg-bukkenItem"]/table/tr[th[span[text()="築年"]]]/td[1]/text()',
            "land_right" => '//*[@id="prg-bukkenItem"]/table/tr[th[span[text()="土地権利"]]]/td[2]/text()',
            "city_planning" => '//*[@id="prg-bukkenItem"]/table/tr[th[span[text()="都市計画"]]]/td[1]/text()',
            "restricted_zone" => '//*[@id="prg-bukkenItem"]/table/tr[th[span[text()="用途地域"]]]/td[1]/text()',
            "coverage_ratio" => '//*[@id="prg-bukkenItem"]/table/tr[th[span[text()="建ぺい率／容積率"]]]/td[1]/text()', //※要特殊対応
            "floor_area_ratio" => '//*[@id="prg-bukkenItem"]/table/tr[th[span[text()="建ぺい率／容積率"]]]/td[1]/text()', //※要特殊対応
            "parking_lot" => '//*[@id="prg-bukkenItem"]/table/tr[th[span[text()="駐車場"]]]/td[1]/text()',
            "motorcycle_place" => self::NO_SCRAP_ITEM_XPATH,
            "bicycle_parking_lot" => self::NO_SCRAP_ITEM_XPATH,
            "private_road_burden_area" => self::NO_SCRAP_ITEM_XPATH,
            "contact_with_road" => '//*[@id="prg-bukkenItem"]/table/tr[th[span[text()="接道状況"]]]/td[1]/text()',
            "classification_of_land_and_category" => '//*[@id="prg-bukkenItem"]/table/tr[th[span[text()="地目"]]]/td[2]/text()',
            "geographical_features" => self::NO_SCRAP_ITEM_XPATH,
            "the_total_number_of_houses" => '//*[@id="prg-bukkenItem"]/table/tr[th[span[text()="総区画／戸数"]]]/td[1]/text()',
            "country_law_report" => '//*[@id="prg-bukkenItem"]/table/tr[th[span[text()="国土法届出"]]]/td[2]/text()',
            "reform_and_renovation_important_notice" => self::NO_SCRAP_ITEM_XPATH,
            "special_report" => self::NO_SCRAP_ITEM_XPATH,
            "facilities" => '//*[@id="prg-bukkenItem"]/table/tr[th[span[text()="設備・条件"]]]/td[1]/text()',
            "remarks" => '//*[@id="prg-bukkenItem"]/table/tr[th[span[text()="備考"]]]/td[1]/text()',
            "conditions" => self::NO_SCRAP_ITEM_XPATH,
            "present_condition" => '//*[@id="prg-bukkenItem"]/table/tr[th[span[text()="現況"]]]/td[1]/text()',
            "delivery" => '//*[@id="prg-bukkenItem"]/table/tr[th[span[text()="引渡"]]]/td[2]/text()',
            "article_number" => '//*[@id="handleCompany"]/@data-b',
            "information_pub_date" => '//*[@id="bukkenDetailInfo"]/p[@class="expirationDate"]/span[contains(text(), "情報登録日")]/text()', //要特殊対応
            "next_time_update_due_date" => '//*[@id="bukkenDetailInfo"]/p[@class="expirationDate"]/span[contains(text(), "有効期限")]/text()', //要特殊対応
            "business_form" => '//*[@id="handleCompany"]/div[@class="companyInfo"]/div[@class="companyDetail"]/p[@class="companyType"]/text()', //要特殊対応
            "occupied_area" => '//*[@id="bukkenDetailInfo"]/div[@class="bukkenDetail"]/div[@class="bukkenValueInfo"]/table/tr[th[text()="専有面積"]]/td[1]/text()',
            "balcony" => '//*[@id="prg-bukkenItem"]/table/tr[th[span[text()="バルコニー面積"]]]/td[2]/text()',
            "pet" => self::NO_SCRAP_ITEM_XPATH,
            "site_area" => self::NO_SCRAP_ITEM_XPATH,
            "management_form" => self::NO_SCRAP_ITEM_XPATH,
            "cost_per_square_meter" => self::NO_SCRAP_ITEM_XPATH,
            
        ];
    private $itemImageXPathDic = [
        "image_url1" => '//a[contains(@id, "BukkenPhoto_")]/img/@src', //要特殊対応。widthとheightを800に変える
    ];
    private $is404XPath = '//*[@id="expired"]/div/p[1]';
    const ARTICLE_TYPE = ['売りアパート','区分マンション','一棟マンション','収益ビル','店舗・事務所・その他','土地'];
    
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
        /**
         * Home'sの仕様で、ページングが100ページまでしか辿れない為、
         * 都道府県×物件種別で予め絞り込んだURLからクロールを開始する
         */
        $preList = self::PREFECTURE_LIST;
        array_walk($preList, function ($pre) {
            $articleType = self::ARTICLE_TYPE;
            array_walk($articleType, function ($type, $_, $pre){
                $preUrl = $this->baseUrl.'/'.urlencode($pre['tan'].'の'.$type).'/?sortKey=new_date&sortDirection=1';
                $this->logger->addInfo("次の県＆種別：{$preUrl}");
                $this->getDetailListUrl($preUrl);
            }, $pre);
        });
    }
    
    private function getDetailListUrl($detailUrl) {
        $client = MyClient::getPreparedClient(); 
        $crawler = $client->request('GET', $detailUrl);
        // PRエリアを除くため、親のdivから特定
        $detailTargetSelector = '//div[@class="defaultArea"]/div/div/div/div/p[@class="listDetailTitle"]/a/@href'; // 詳細ページURL

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
        $nextTargetSelector = '//a[@title="次のページを見る"]/@href';
        $nextUrl = $crawler->filterXPath($nextTargetSelector)->each(function ($node) {
            $nextUrl = $this->domainUrl . $node->text() . '&sortKey=new_date&sortDirection=1';
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
                        $images[] = preg_replace('/(width|height)=[0-9]+/', '$1=800', trim($text));
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
        return ($crawler->filterXPath($this->is404XPath)->each(function ($node) {
            return ($node->text() == 'お探しの物件は掲載期限を終了いたしました。');
        }))? true : false;
    }
}
