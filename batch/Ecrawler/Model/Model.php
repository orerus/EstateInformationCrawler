<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Ecrawler\Model;

/**
 * Description of Model
 *
 * @author murata_sho
 */
abstract class Model {
    protected $pdo;
    const NO_SCRAP_ITEM = "-";
    
    function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * DBへ入れることのできる文字列にして返す
     * 連続する空白を１つの空白に置換し、DBのカラムサイズ以上の文字列をカットして返す
     * @param string $value
     * @return string
     */
    public function format($value, $max) {
        return mb_substr(preg_replace('/\s\s+/', ' ', $value), 0, $max);
    }
}
