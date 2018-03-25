<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Ecrawler\Model;

use PDO;
use Imagick;

/**
 * Description of ArticleImage
 *
 * @author murata_sho
 */
class ArticleImage extends Model {
    const GET_IMAGE_WAIT = 100000; //500000で0.5秒
    const IMAGE_CACHE_DIR = "/Crawl_Data/images/";
    const IMAGE_URL_COLUMN_SIZE = 1000;
    const INSERT_IMAGE_SQL = "INSERT INTO crawled_images (`article_id`, `url`, `created`, `updated`) VALUES(:article_id, :url, now(), now())";

    /**
     * 有効なURLかどうかを返す
     * 
     * @param string $url
     * @return bool
     */
    public static function isValidUrl($url) {
        return (strlen($url) <= self::IMAGE_URL_COLUMN_SIZE)? true : false;
    }
    
    /**
     * 画像保存
     * 
     * @param int $insertId
     * @param string $url
     * @param int $index そのサイトの何番目の画像か
     * @return bool DBに保存できたかどうか。（falseでも実画像は保存できているかもしれない）
     */
    public function saveImage($insertId, $url, $index) {
        $dirPath = self::IMAGE_CACHE_DIR.$insertId;
        if (!file_exists($dirPath)) { mkdir($dirPath); }
        if ($url === self::NO_SCRAP_ITEM) {
            return false;
        }
        // 0.5秒遅延
        usleep(self::GET_IMAGE_WAIT);
        // 画像の拡張子をjpegに変換して保存
        $im = new Imagick($url);
        $im->setImageFormat("jpg");
        $im->writeImage("{$dirPath}/{$index}.jpg");

        if (!self::isValidUrl($url)) {
            // URL長すぎスキップ
            return false;
        }
        $stmt = $this->pdo->prepare(self::INSERT_IMAGE_SQL);
        $stmt->bindValue(':article_id', $insertId, PDO::PARAM_INT);
        $stmt->bindValue(':url', $url, PDO::PARAM_STR);
        return $stmt->execute();
    }
}
