<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Ecrawler\Model;

use Ecrawler\Entity\UrlQueue AS Queue;
use PDO;
/**
 * Description of UrlQueue
 *
 * @author murata_sho
 */
class UrlQueue extends Model {
    const URL_COLUMN_SIZE = 250;
    const SITE_ID_SIZE = 4;
    const INSERT_SQL = <<<SQL
INSERT INTO crawl_url_queue (
    `url`, `site_id`
) VALUES (
    :url, :site_id
)
SQL;
    
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
     * クロールURLキューから削除する
     * 
     * @param type $pdo
     * @param type $url
     * @return bool
     */
    public function deleteUrlQuere($url) {
        $deleteStmt = $this->pdo->prepare("DELETE FROM crawl_url_queue WHERE url = :url");
        return $deleteStmt->execute(["url" => $url]);
    }
    
    /**
     * クロール対象URLの保存
     * 
     * @param UrlQueue $queue URLキュー
     * @return bool
     */
    public function save(Queue $queue) {
        if (!self::isValidUrl($queue->get("url"))) {
            // URL長すぎスキップ
            return false;
        } 
        $stmt = $this->pdo->prepare(self::INSERT_SQL);
        $stmt->bindValue(":url", $this->format($queue->get("url"), self::URL_COLUMN_SIZE), PDO::PARAM_STR);
        $stmt->bindValue(":site_id", $this->format($queue->get("site_id"), self::SITE_ID_SIZE), PDO::PARAM_INT);
        return $stmt->execute();
    }
    
    /**
     * 次のクロールURLをsize個取得
     * 
     * @param type $siteId
     * @param type $size
     * @return type
     */
    public function popNextQueues($siteId, $size) {
        $selectStmt = $this->pdo->prepare("SELECT url FROM crawl_url_queue WHERE site_id = :site_id ORDER BY id LIMIT :size");
        $selectStmt->bindValue(":site_id", $siteId, PDO::PARAM_INT);
        $selectStmt->bindValue(":size", $size, PDO::PARAM_INT);
        $selectStmt->execute();
        return $selectStmt;
    }
    
    /**
     * 残キュー数を返す
     * 
     * @param type $siteId
     * @return type
     */
    public function countQueues($siteId) {
        $checkStmt = $this->pdo->prepare('SELECT COUNT(id) FROM crawl_url_queue WHERE site_id = :site_id');
        $checkStmt->bindValue(":site_id", $siteId, PDO::PARAM_INT);
        $checkStmt -> execute();
        $count = $checkStmt -> fetchColumn();
        return $count;
    }
}
