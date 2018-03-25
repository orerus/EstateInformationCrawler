<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Ecrawler\Model;

use PDO;
use Ecrawler\Entity\ArticleInfo;
/**
 * Description of Article
 *
 * @author murata_sho
 */
class Article extends Model{
    const URL_COLUMN_SIZE = 250;
    const ITEM_COLUMN_SIZE = 200;
    const COUNT_SQL = <<<SQL
SELECT COUNT(id) FROM crawled_article_info WHERE url = :url;
SQL;
    const DELETE_SQL = <<<SQL
DELETE FROM crawled_article_info WHERE url = :url;
SQL;
    const INSERT_SQL = <<<SQL
INSERT INTO crawled_article_info (
    `site`, `url`, `name`, `traffic`, `location`, `article_item`, `price`, `administrative_expense`, `repair_financial_reserve`, `tenancy`, `premium`, `deposit`, `maintenance_cost`, `lump_sum`, `annual_planned_income`, `yield`, `layout`, `storey`, `building_area`, `land_area`, `building_structure`, `time_old`, `land_right`, `city_planning`, `restricted_zone`, `coverage_ratio`, `floor_area_ratio`, `parking_lot`, `motorcycle_place`, `bicycle_parking_lot`, `private_road_burden_area`, `contact_with_road`, `classification_of_land_and_category`, `geographical_features`, `the_total_number_of_houses`, `country_law_report`, `reform_and_renovation_important_notice`, `special_report`, `facilities`, `remarks`, `conditions`, `present_condition`, `delivery`, `article_number`, `information_pub_date`, `next_time_update_due_date`, `business_form`,
    `occupied_area`, `balcony`, `pet`, `site_area`, `management_form`, `cost_per_square_meter`, `setback`, `management_company`, `management_employees`,
    `main_opening`, `other_area`
) 
VALUES (
    :site, :url, :name, :traffic, :location, :article_item, :price, :administrative_expense, :repair_financial_reserve, :tenancy, :premium, :deposit, :maintenance_cost, :lump_sum, :annual_planned_income, :yield, :layout, :storey, :building_area, :land_area, :building_structure, :time_old, :land_right, :city_planning, :restricted_zone, :coverage_ratio, :floor_area_ratio, :parking_lot, :motorcycle_place, :bicycle_parking_lot, :private_road_burden_area, :contact_with_road, :classification_of_land_and_category, :geographical_features, :the_total_number_of_houses, :country_law_report, :reform_and_renovation_important_notice, :special_report, :facilities, :remarks, :conditions, :present_condition, :delivery, :article_number, :information_pub_date, :next_time_update_due_date, :business_form,
    :occupied_area, :balcony, :pet, :site_area, :management_form, :cost_per_square_meter, :setback, :management_company, :management_employees,
    :main_opening, :other_area
);
SQL;
    const DELETE_CHECK_SQL = <<<SQL
SELECT id, url FROM crawled_article_info WHERE site = :site AND deleted = 0 AND MOD(id, 7) = :weekday;
SQL;
    const MAKE_DELETE_FLAG_SQL = <<<SQL
UPDATE crawled_article_info SET deleted = 1 WHERE id = :id;            
SQL;
    /**
     * 既に保存済みの物件かどうかを返す
     * 
     * @param type $url
     * @return bool
     */
    public function isSavedArticle($url) {
        $checkStmt = $this->pdo->prepare(self::COUNT_SQL);
        $checkStmt -> execute(['url' => $url]);
        $count = $checkStmt -> fetchColumn();
        return ($count)? true : false;
    }
    
    /**
     * 物件情報を削除する
     * 
     * @param type $url
     * @return bool
     */
    public function deleteSavedUrl($url) {
        $stmt = $this->pdo->prepare(self::DELETE_SQL);
        return $stmt->execute(['url' => $url]);
    }
    
    /**
     * 有効なURLかどうかを返す
     * 
     * @param string $url
     * @return bool
     */
    public static function isValidUrl($url) {
        return (strlen($url) <= self::URL_COLUMN_SIZE)? true : false;
    }
    
    /**
     * クロール物件情報保存処理
     * 
     * @param ArticleInfo $article
     * @return bool
     */
    public function save(ArticleInfo $article) {
        $stmt = $this->pdo->prepare(self::INSERT_SQL);
        foreach($article->getAllProperties() as $key => $val) {
            switch ($key) {
                case "id":
                    break;
                case "site":
                    $stmt->bindValue($key, $this->format($article->get($key), self::URL_COLUMN_SIZE), PDO::PARAM_INT);
                    break;
                case "created":
                case "updated":
                case "default":
                    break;
                default:
                    // パラメータバインド
                    $correctedVal = (is_null($val) || strlen($val) == 0)? self::NO_SCRAP_ITEM : $this->format($article->get($key), self::ITEM_COLUMN_SIZE);
                    $stmt->bindValue($key, $correctedVal, PDO::PARAM_STR);
                    break;
            }
        }
        return $stmt->execute();
    }
    
    public function getDeleteCheckArticleGenerator($siteId) {
        $stmt = $this->pdo->prepare(self::DELETE_CHECK_SQL);
        $stmt->execute(['site' => $siteId, 'weekday'=>date('w')]);
        while ($row = $stmt -> fetch(PDO::FETCH_ASSOC)) {
            yield $row;
        }
    }
    
    public function makeDeleteFlag($id) {
        $stmt = $this->pdo->prepare(self::MAKE_DELETE_FLAG_SQL);
        return $stmt->execute(['id' => $id]);
    }
}
