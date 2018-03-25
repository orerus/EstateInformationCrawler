<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once(dirname(__FILE__) . '/vendor/autoload.php');
use Goutte;
$client = new Goutte\Client();  
$crawler = $client->request('GET', "http://toushi-athome.jp/ei_41/dtl_6960061848/?DOWN=1&BKLISTID=001MPC&SEARCHDIV=1");
$tempCrawler = $crawler->filterXPath("//td");