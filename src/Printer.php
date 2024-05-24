<?php

namespace xuezhitech\xpyun;

use mysql_xdevapi\Exception;
use xuezhitech\xpyun\Util\Curl;

class Printer
{
    private $curl = null;

    protected array $config = [
        'user'=>'',
        'key'=>'',
        'debug'=>'0',
        'header'=>'Content-Type:application/json;charset=UTF-8'
    ];

    public function __construct( $config=[] )
    {
        $this->config = array_merge($this->config,$config);
        $this->curl = new Curl();
    }

    /**
     * 添加打印机到开发者账户
     */
    public function addPrinters($item=[])
    {
        $timestamp = time();
        $url = 'https://open.xpyun.net/api/openapi/xprinter/addPrinters';
        if ( empty($items) ) {
            throw new Exception('设备信息不能为空!');
        }
        $data = [
            'user' => $this->config['user'],
            'timestamp'=> $timestamp,
            'sign' => $this->getSign($timestamp),
            'debug' => $this->config['debug'],
            'items' => json_encode($items),
        ];
        return json_decode($this->curl->getCurlInfo($url,json_encode($data)));
    }

    private function getSign(string $timestamp): string
    {
        if ( empty($this->config['user'])) {
            throw new Exception('开发者Id为空');
        }
        if ( empty($this->config['key'])) {
            throw new Exception('开发者KEY为空');
        }
        return sha1($this->config['user'].$this->config['key'].$timestamp);
    }

}
