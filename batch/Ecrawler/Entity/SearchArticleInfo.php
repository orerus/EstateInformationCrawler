<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Ecrawler\Entity;

/**
 * Description of SearchArticleInfo
 *
 * @author murata_sho
 */
class SearchArticleInfo extends Entity{
    protected $article_id;
    protected $prefecture_id;
    protected $article_type_id;
    protected $yield;
    protected $price;
    protected $pub_date;
}
