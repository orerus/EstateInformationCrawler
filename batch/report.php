<?php
require_once(dirname(__FILE__) . '/vendor/autoload.php');

const COUNT_SQL = <<<SQL
SELECT 
cs.id, 
cs.`name`,
count(csai.id) as count,
ifnull(T.count, 0) as prevCount
FROM 
crawled_search_article_info csai
INNER JOIN 
crawled_article_info cai ON csai.crawled_article_info_id = cai.id
INNER JOIN
crawled_site cs ON cs.id = cai.site
LEFT OUTER JOIN 
(SELECT 
site_id,
count
FROM crawled_count_history
WHERE summary_date = DATE_SUB( CURDATE(),INTERVAL 1 DAY )
GROUP BY site_id
ORDER BY site_id, created
) T ON cs.id = T.site_id
GROUP BY
cs.id,
cs.`name`
;
SQL;

const INSERT_SQL = <<<SQL
INSERT INTO crawled_count_history 
(summary_date, site_id, count)
VALUES
(:date, :site_id, :count);
SQL;

class ArticleSiteData
{
    public $id;
    public $name;
    public $count;
    
    function __construct($id, $name, $count) {
        $this->id = $id;
        $this->name = $name;
        $this->count = $count;
    }
    
    function __toString() {
        return "[CrawledSite] id: $this->id, name: $this->name, count: $this->count";
    }
}

function postFromHTTP($url, $data) {
	$options = array(
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_AUTOREFERER => true,
	);
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($ch, CURLOPT_VERBOSE, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt_array($ch, $options);
	$result = curl_exec($ch);
	curl_close($ch);
	return $result;
}

function saveCount($pdo, $sql, $date, $siteId, $count) {
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(":date", $date, PDO::PARAM_STR);
    $stmt->bindValue(":site_id", $siteId, PDO::PARAM_INT);
    $stmt->bindValue(":count", $count, PDO::PARAM_INT);
    return $stmt->execute();
}

try {
    $pdo = Environment::getPDOInstance();
    $payload = ["text" => "今日の各サイト毎の登録件数でござるぅ", "attachments" => []];
    $fields = [];
    $yesterday = date('Y-m-d', strtotime('yesterday'));
    $today = date('Y-m-d');
    

    foreach($pdo->query(COUNT_SQL) as $row) {
       $fields[] = ["title" => $row['name'], "value" => $row['count']." (前日:".$row['prevCount']." 差分: ".($row['count'] - $row['prevCount']).")", "short" => false];
       saveCount($pdo, INSERT_SQL, $today, $row['id'], $row['count']);
    }
    $payload["attachments"][] = ["fields" => $fields];
    
//    var_export(json_encode($payload, JSON_UNESCAPED_UNICODE));
    
    echo "送信結果: ".postFromHTTP(Environment::getArticleCountNotificationURL(), json_encode($payload, JSON_UNESCAPED_UNICODE));
    
} catch (Exception $e) {
    // TODO エラー原因をslackへ通知
    die();
}