<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Ecrawler\SiteCrawler;

/**
 * Class MyClient
 *
 * HTTPクライアント
 */
class MyClient extends \Goutte\Client
{
    /**
     * @var array $ua  ユーザーエージェントのリスト
     */
    protected $ua = [
        'pc.chrome' => 'Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/49.0.2623.110 Safari/537.36',
        'sp.safari' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 9_1 like Mac OS X) AppleWebKit/601.1.46 (KHTML, like Gecko) Version/9.0 Mobile/13B143 Safari/601.1',
        'bot.google' => 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)',
    ];

    /**
     * ユーザーエージェントを切り替える
     *
     * @param  string  $platform
     * @return MyClient
     */
    public function setUserAgent($platform)
    {
        $this->setServerParameters(['HTTP_USER_AGENT' => $this->ua[$platform]]);

        return $this;
    }
    
    /**
     * UserAgentセット済みのClientを返す
     * @return \Ecrawler\SiteCrawler\MyClient
     */
    public static function getPreparedClient() {
        $client = new MyClient();
        $client->setUserAgent('bot.google');
        return $client;
    }
}