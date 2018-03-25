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
class Mitsui extends BaseSiteCrawler {
    private $baseUrls = [
        'http://www.rehouse.co.jp/investBukkenList/?page=1&sortType=&pagingType=20&todoufukenCd1=&sikutyousonCd1=&todoufukenCd2=&sikutyousonCd2=&todoufukenCd3=&sikutyousonCd3=&todofukenCd=&ensenCd1=&ekiCd1=&ensenCd2=&ekiCd2=&ensenCd3=&ekiCd3=&tokusyu=&kensakuNo=&autoFlg=&bukkenSb=&way=2&kakakuF=0&kakakuT=0&senyuF=&tatemonoF=&tochiF=&chijo=&shozaiKai=&soukosuu=&hikiYYmm=&kenchikuJouken=&chusya=&gazo1=&gazo2=&souteirimawari=0&reform1=&reformFlg1=&chikunen=0&toho=0&kenri=0&newBukkenFlg=0&rhIvGyoumuFlg=&rhIvToushiFlg=1&quickFlg=&topFlg=1&lang=&caseId=',
    ];
    private $domainUrl = 'http://www.rehouse.co.jp/';
    private $noPrintingImg = 'pic_now_printing.gif';
    protected $siteId = 6;
    private $logger;
    private $itemXPathDic = [
            "name" => '//h1/span/text()',
            "traffic" => '//th[text()="交通："]/following-sibling::td[1]/.', //連続したスペースを詰める必要がある
            "location" => '//th[text()="所在地："]/following-sibling::td[1]/.',
            "article_item" => '//h1/img/@alt',
            "price" => '//*[@id="mainArea"]//p/strong[text()="価格："]/following-sibling::span/.',
            "administrative_expense" => '//th[text()="管理費"]/following-sibling::td[1]/text()',
            "repair_financial_reserve" => '//th[text()="修繕積立金"]/following-sibling::td[1]/text()',
            "tenancy" => self::NO_SCRAP_ITEM_XPATH,
            "premium" => self::NO_SCRAP_ITEM_XPATH,
            "deposit" => self::NO_SCRAP_ITEM_XPATH,
            "maintenance_cost" => self::NO_SCRAP_ITEM_XPATH,
            "lump_sum" => self::NO_SCRAP_ITEM_XPATH,
            "annual_planned_income" => '//th[text()="現行賃料（年間）"]/following-sibling::td[1]/text()',
            "yield" => '//th[text()="現行利回り"]/following-sibling::td[1]/text()',
            "layout" => '//th[text()="間取り"]/following-sibling::td[1]/text()',
            "storey" => '//th[text()="階数／階建"]/following-sibling::td[1]/text()',
            "building_area" => '//th[text()="建物面積"]/following-sibling::td[1]/text()', // 建物面積
            "land_area" => '//th[text()="土地面積"]/following-sibling::td[1]/text()', // 土地面積
            "building_structure" => '//th[text()="建物構造"]/following-sibling::td[1]/text()',
            "time_old" => '//th[text()="築年月"]/following-sibling::td[1]/text()',
            "land_right" => '//th[text()="土地権利"]/following-sibling::td[1]/text()',
            "city_planning" => '//th[text()="都市計画"]/following-sibling::td[1]/text()',
            "restricted_zone" => '//th[text()="用途地域"]/following-sibling::td[1]/text()',
            "coverage_ratio" => '//th[text()="建ぺい率"]/following-sibling::td[1]/text()',
            "floor_area_ratio" => 'th[text()="容積率"]/following-sibling::td[1]/text()',
            "parking_lot" => '//th[text()="駐車場"]/following-sibling::td[1]/text()',
            "motorcycle_place" => self::NO_SCRAP_ITEM_XPATH,
            "bicycle_parking_lot" => self::NO_SCRAP_ITEM_XPATH,
            "private_road_burden_area" => self::NO_SCRAP_ITEM_XPATH,
            "contact_with_road" => '//th[text()="接道状況"]/following-sibling::td[1]/text()',
            "classification_of_land_and_category" => '//th[text()="地目"]/following-sibling::td[1]/text()',
            "geographical_features" => self::NO_SCRAP_ITEM_XPATH,
            "the_total_number_of_houses" => '//th[text()="総戸数"]/following-sibling::td[1]/text()',
            "country_law_report" => self::NO_SCRAP_ITEM_XPATH,
            "reform_and_renovation_important_notice" => self::NO_SCRAP_ITEM_XPATH,
            "special_report" => self::NO_SCRAP_ITEM_XPATH,
            "facilities" => self::NO_SCRAP_ITEM_XPATH,
            "remarks" => '//th[text()="備考"]/following-sibling::td[1]/.',
            "conditions" => self::NO_SCRAP_ITEM_XPATH,
            "present_condition" => '//th[text()="現況"]/following-sibling::td[1]/text()',
            "delivery" => '//th[text()="引渡時期"]/following-sibling::td[1]/text()',
            "article_number" => self::NO_SCRAP_ITEM_XPATH,
            "information_pub_date" => '//th[text()="更新日"]/following-sibling::td[1]/text()',
            "next_time_update_due_date" => '//th[text()="次回更新予定日"]/following-sibling::td[1]/text()',
            "business_form" => '//th[text()="取引態様"]/following-sibling::td[1]/text()',
            "occupied_area" => '//th[text()="専有面積"]/following-sibling::td[1]/text()', // 専有面積
            "balcony" => self::NO_SCRAP_ITEM_XPATH,
            "pet" => self::NO_SCRAP_ITEM_XPATH,
            "site_area" => self::NO_SCRAP_ITEM_XPATH,
            "management_form" => '//th[text()="管理方式"]/following-sibling::td[1]/text()',
            "cost_per_square_meter" => self::NO_SCRAP_ITEM_XPATH,
            "setback" => self::NO_SCRAP_ITEM_XPATH,
            "management_company" => '//th[text()="管理会社"]/following-sibling::td[1]/text()',
            "management_employees" => self::NO_SCRAP_ITEM_XPATH,
            "main_opening" => self::NO_SCRAP_ITEM_XPATH,
            "other_area" => self::NO_SCRAP_ITEM_XPATH,
        ];
    private $itemImageXPathDic = [
        "image_url1" => '//*[@id="zoomImgItem"]/a',
    ];
    private $is404XPath = '//*[@id="mainArea"]//p/strong[text()="価格："]/following-sibling::span';
    
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
    
    private function getDetailListUrl($detailUrl, $page=1) {
        $client = MyClient::getPreparedClient();  
        $crawler = $client->request('GET', $detailUrl);
        // PRエリアを除くため、親のdivから特定
        $detailTargetSelector = '//*[@id="bukkenDetail"]/@href'; // 詳細ページURL

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
        $nextTargetSelector = '//*[@id="linkPage" and text()="次へ"]';
        $nextUrl = $crawler->filterXPath($nextTargetSelector);
        if (count($nextUrl) > 0) {
            $nextPage = $page + 1;
            // 1秒遅延
            usleep(self::SLEEP_MICRO_SECOND);
            $this->getDetailListUrl('http://www.rehouse.co.jp/investBukkenList/?page='.$nextPage.'&sortType=&pagingType=20&todoufukenCd1=&sikutyousonCd1=&todoufukenCd2=&sikutyousonCd2=&todoufukenCd3=&sikutyousonCd3=&todofukenCd=&ensenCd1=&ekiCd1=&ensenCd2=&ekiCd2=&ensenCd3=&ekiCd3=&tokusyu=&kensakuNo=&autoFlg=&bukkenSb=&way=2&kakakuF=0&kakakuT=0&senyuF=&tatemonoF=&tochiF=&chijo=&shozaiKai=&soukosuu=&hikiYYmm=&kenchikuJouken=&chusya=&gazo1=&gazo2=&souteirimawari=0&reform1=&reformFlg1=&chikunen=0&toho=0&kenri=0&newBukkenFlg=0&rhIvGyoumuFlg=&rhIvToushiFlg=1&quickFlg=&topFlg=1&lang=&caseId=',
                    $nextPage);
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
                    // 連続した空白を詰めてtrimしている
                    $article->set($key, trim(preg_replace('/\s+/', ' ', $dom->text())));
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
                $urls = $crawler->filterXPath($xpath)->extract('href');
                foreach ($urls as $text) {
                    if (!empty($text) && strpos($text, $this->noPrintingImg) === false) {
                        $images[] = 'http:'.trim($text);
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
        // この物件は掲載終了ページも200で返ってくる為、
        // 価格が取れなければ404とみなす
        $dom = $crawler->filterXPath($this->is404XPath);
        return ($dom->count())? false : true;
    }
}
