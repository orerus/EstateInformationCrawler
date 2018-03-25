<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * 環境ごとに代わる情報を取得するためのクラス
 */
class Environment {
    public static function getPDOInstance() {
        $pdo = new PDO('mysql:host=localhost;dbname=hoge;charset=utf8',
                    'root','hogefuga');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        // デフォルトで連想配列に変換
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $pdo;
    }
    
    public static function getSentryDSN() {
        return 'http://hogefuga';
    }
    
    public static function getArticleCountNotificationURL() {
        return 'https://hooks.slack.com/services/hoge/fugafuga';
    }
}
