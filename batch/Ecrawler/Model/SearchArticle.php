<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Ecrawler\Model;

use Ecrawler\Entity\SearchArticleInfo;
use PDO;

/**
 * Description of SearchArticle
 *
 * @author murata_sho
 */
class SearchArticle extends Model {
    const INSERT_SQL = <<<SQL
INSERT INTO crawled_search_article_info 
    (`id`, `prefecture_id`, `article_type_id`, `yield`, `price`, `information_pub_date`) 
VALUES
    (:article_id, :prefecture_id, :article_type_id, :yield, :price, :pub_date);
SQL;
    const MAKE_DELETE_FLAG_SQL = <<<SQL
UPDATE crawled_search_article_info SET deleted = 1 WHERE crawled_article_info_id = :id;            
SQL;

    const DELETE_SQL = <<<SQL
DELETE FROM crawled_search_article_info WHERE crawled_article_info_id = :id;            
SQL;

    // DB接続回数削減のためにここでも定義
    const PREFECTURE_LIST = 
            array (
                '北海道'=>['id'=>1, 'kana'=>'ホッカイドウ'],
                '青森県'=>['id'=>2, 'kana'=>'アオモリケン'],
                '岩手県'=>['id'=>3, 'kana'=>'イワテケン'],
                '宮城県'=>['id'=>4, 'kana'=>'ミヤギケン'],
                '秋田県'=>['id'=>5, 'kana'=>'アキタケン'],
                '山形県'=>['id'=>6, 'kana'=>'ヤマガタケン'],
                '福島県'=>['id'=>7, 'kana'=>'フクシマケン'],
                '茨城県'=>['id'=>8, 'kana'=>'イバラキケン'],
                '栃木県'=>['id'=>9, 'kana'=>'トチギケン'],
                '群馬県'=>['id'=>10, 'kana'=>'グンマケン'],
                '埼玉県'=>['id'=>11, 'kana'=>'サイタマケン'],
                '千葉県'=>['id'=>12, 'kana'=>'チバケン'],
                '東京都'=>['id'=>13, 'kana'=>'トウキョウト'],
                '神奈川県'=>['id'=>14, 'kana'=>'カナガワケン'],
                '新潟県'=>['id'=>15, 'kana'=>'ニイガタケン'],
                '富山県'=>['id'=>16, 'kana'=>'トヤマケン'],
                '石川県'=>['id'=>17, 'kana'=>'イシカワケン'],
                '福井県'=>['id'=>18, 'kana'=>'フクイケン'],
                '山梨県'=>['id'=>19, 'kana'=>'ヤマナシケン'],
                '長野県'=>['id'=>20, 'kana'=>'ナガノケン'],
                '岐阜県'=>['id'=>21, 'kana'=>'ギフケン'],
                '静岡県'=>['id'=>22, 'kana'=>'シズオカケン'],
                '愛知県'=>['id'=>23, 'kana'=>'アイチケン'],
                '三重県'=>['id'=>24, 'kana'=>'ミエケン'],
                '滋賀県'=>['id'=>25, 'kana'=>'シガケン'],
                '京都府'=>['id'=>26, 'kana'=>'キョウトフ'],
                '大阪府'=>['id'=>27, 'kana'=>'オオサカフ'],
                '兵庫県'=>['id'=>28, 'kana'=>'ヒョウゴケン'],
                '奈良県'=>['id'=>29, 'kana'=>'ナラケン'],
                '和歌山県'=>['id'=>30, 'kana'=>'ワカヤマケン'],
                '鳥取県'=>['id'=>31, 'kana'=>'トットリケン'],
                '島根県'=>['id'=>32, 'kana'=>'シマネケン'],
                '岡山県'=>['id'=>33, 'kana'=>'オカヤマケン'],
                '広島県'=>['id'=>34, 'kana'=>'ヒロシマケン'],
                '山口県'=>['id'=>35, 'kana'=>'ヤマグチケン'],
                '徳島県'=>['id'=>36, 'kana'=>'トクシマケン'],
                '香川県'=>['id'=>37, 'kana'=>'カガワケン'],
                '愛媛県'=>['id'=>38, 'kana'=>'エヒメケン'],
                '高知県'=>['id'=>39, 'kana'=>'コウチケン'],
                '福岡県'=>['id'=>40, 'kana'=>'フクオカケン'],
                '佐賀県'=>['id'=>41, 'kana'=>'サガケン'],
                '長崎県'=>['id'=>42, 'kana'=>'ナガサキケン'],
                '熊本県'=>['id'=>43, 'kana'=>'クマモトケン'],
                '大分県'=>['id'=>44, 'kana'=>'オオイタケン'],
                '宮崎県'=>['id'=>45, 'kana'=>'ミヤザキケン'],
                '鹿児島県'=>['id'=>46, 'kana'=>'カゴシマケン'],
                '沖縄県'=>['id'=>47, 'kana'=>'オキナワケン'],
            );
    const ARTICLE_TYPE = 
            [
                '1棟マンション' => ['id'=>1],
                '一棟売りマンション' => ['id'=>1],
                '1棟アパート'   => ['id'=>2],
                '売りアパート'   => ['id'=>2],
                '1棟商業ビル'   => ['id'=>3],
                '戸建賃貸'      => ['id'=>4],
                '賃貸併用住宅'  => ['id'=>5],
                '倉庫'          => ['id'=>6],
                '工場'          => ['id'=>7],
                '駐車場'        => ['id'=>8],
                'ホテル'        => ['id'=>9],
                '区分マンション' => ['id'=>10],
                '中古マンション' => ['id'=>10],
                '新築マンション' => ['id'=>10],
                '区分店舗'      => ['id'=>11],
                '区分事務所'    => ['id'=>12],
                '土地'          => ['id'=>13],
                'その他'        => ['id'=>14],
            ];
    
    public function save(SearchArticleInfo $article) {
        $stmt = $this->pdo->prepare(self::INSERT_SQL);
        $stmt->bindValue(':article_id', $article->get('article_id'), PDO::PARAM_INT); 
        $stmt->bindValue(':prefecture_id', $article->get('prefecture_id'), PDO::PARAM_INT);
        $stmt->bindValue(':article_type_id', $article->get('article_type_id'), PDO::PARAM_INT);
        $stmt->bindValue(':yield', $article->get('yield'), PDO::PARAM_INT);
        $stmt->bindValue(':price', $article->get('price'), PDO::PARAM_INT);
        if (empty($article->get('pub_date'))) {
            $stmt->bindValue(':pub_date', null, PDO::PARAM_NULL);        
        } else {
            $stmt->bindValue(':pub_date', $article->get('pub_date'), PDO::PARAM_STR);
        }
        return $stmt->execute();
    }
    
    /**
     * 都道府県名からIDを返す
     * 
     * @param type $prefecture
     * @return type
     */
    public static function getPrefectureId($prefecture) {
        if (array_key_exists($prefecture, self::PREFECTURE_LIST)) {
            $array = self::PREFECTURE_LIST;
            return $array[$prefecture]['id'];
        }
        return null;
    }
    
    /**
     * 住所全体から都道府県を抽出する
     * 
     * @param type $address
     * @return type
     */
    public static function getPrefecture($address) {
        if (preg_match('/^(.{2,3}?[都道府県])/u', $address, $match)) {
            return $match[1];
        }
        return null;
    }
    
    /**
     * 物件種別IDを返す
     * 
     * @param type $type
     * @return type
     */
    public static function getArticleTypeId($type) {
        if (array_key_exists($type, self::ARTICLE_TYPE)) {
            $array = self::ARTICLE_TYPE;
            return $array[$type]['id'];
        }
        return null;
    }
    
    /**
     * 金額または％を数値に変換する
     * 
     * @param type $price
     * @return type
     */
    public static function parseNumeric($price) {
        $num = preg_replace('/,/u', '', $price);
        if (preg_match('/^([0-9.]+)([万|億]?)/u', $num, $matches)) {
            switch($matches[2]) {
            case '万':
                return $matches[1] * 10000;
            case '億':
                return $matches[1] * 100000000;
            default :
                return $matches[1] * 1;
            }
        }
        return null;
    }
    
    /**
     * 2xxx年xx月xx日の書式を、MySQLに挿入できる形式に変換する
     * 
     * @param string $date
     * @return string|null
     */
    public static function parseJapaneseDate($date) {
        if (preg_match('/^[12][0-9]{3}年(1[0-2]|0?[1-9])月(3[01]|[12][0-9]|0?[1-9])日\z/u', $date, $matches)) {
            return \DateTime::createFromFormat('Y年m月d日', $date)->format('Y-m-d 00:00:00');
        }
        return null;
    }
    
    public function makeDeleteFlag($id) {
        $stmt = $this->pdo->prepare(self::MAKE_DELETE_FLAG_SQL);
        return $stmt->execute(['id' => $id]);
    }
    
    public function delete($id) {
        $stmt = $this->pdo->prepare(self::DELETE_SQL);
        return $stmt->execute(['id' => $id]);
    }
}
