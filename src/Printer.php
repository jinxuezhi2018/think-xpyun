<?php

namespace xuezhitech\xpyun;

use mysql_xdevapi\Exception;
use xuezhitech\xpyun\Util\Curl;

class Printer
{
    private $curl = null;

    protected array $print_config = [
        'copies'=>1, // 打印份数，默认为1，取值[1-65535]，超出范围将视为无效参数
        'cutter'=>0, // 切刀控制开关，默认为0，开启云端默认设置控制切刀，1关闭云端切刀，若想自己设置切刀位置，可以通过 <CUT> 标签设置切刀 仅用于支持切刀的芯烨云打印机。
        'voice'=>1, // 声音播放模式，0 为取消订单模式，1 为静音模式，2 为来单播放模式，3 为有用户申请退单了，默认为 2 来单播放模式
        'mode'=>0, // 打印模式：值为 0 或不指定则会检查打印机是否在线，如果不在线 则不生成打印订单，直接返回设备不在线状态码；如果在线则生成打印订单，并返回打印订单号。值为 1不检查打印机是否在线，直接生成打印订单，并返回打印订单号。如果打印机不在线，订单将缓存在打印队列中，打印机正常在线时会自动打印。
        'expiresIn'=>0, // 订单有效期，单位：秒。订单打印时，超过该时间的订单将不会自动加载打印，当参数设置为 0 时，将采用系统默认设置值。使用该参数时，需要将参数mode 设置为1。取值范围：0 < expiresIn < 86400。
        'backurlFlag'=>1, // 打印订单状态回调标识。取值范围：[ 1 - 5 ]的整数，对应于芯烨云开放平台管理后台 “功能设置” 菜单界面的打印接口回调标识。
    ];

    protected array $config = [
        'user'=>'',
        'key'=>'',
        'debug'=>'0',
        'header'=>'Content-Type:application/json;charset=UTF-8',
    ];

    public function __construct( $config=[] )
    {
        $this->config = array_merge($this->config,$config);
        $this->curl = new Curl();
    }

    public function getPrinterStatusList():array
    {
        return [
            '0'=>'离线',
            '1'=>'在线',
            '2'=>'异常', //异常一般情况是缺纸，离线的判断是打印机与服务器失去联系超过 30 秒
        ];
    }

    /**
     * 11、批量获取指定打印机状态
     */
    public function queryPrintersStatus($snlist=[]):array
    {
        $timestamp = time();
        $url = 'https://open.xpyun.net/api/openapi/xprinter/queryPrintersStatus';
        if ( empty($snlist) ) {
            throw new Exception('设备SN为空!');
        }
        $data = [
            'user' => $this->config['user'],
            'timestamp' => $timestamp,
            'sign' => $this->getSign($timestamp),
            'debug' => $this->config['debug'],
            'snlist' => $snlist
        ];
        return json_decode($this->curl->getCurlInfo($url,'POST',$this->config['header'],json_encode($data)));
    }

    /**
     * 10、获取指定打印机状态
     */
    public function queryPrinterStatus($sn):array
    {
        $timestamp = time();
        $url = 'https://open.xpyun.net/api/openapi/xprinter/queryPrinterStatus';
        if ( empty($sn) ) {
            throw new Exception('设备SN为空!');
        }
        $data = [
            'user' => $this->config['user'],
            'timestamp' => $timestamp,
            'sign' => $this->getSign($timestamp),
            'debug' => $this->config['debug'],
            'sn' => $sn
        ];
        return json_decode($this->curl->getCurlInfo($url,'POST',$this->config['header'],json_encode($data)));
    }

    /**
     * 9、查询指定打印机某天的订单统计数
     */
    public function queryOrderStatis($sn,$date):array
    {
        $timestamp = time();
        $url = 'https://open.xpyun.net/api/openapi/xprinter/queryOrderStatis';
        if ( empty($sn) ) {
            throw new Exception('设备SN为空!');
        }
        if ( empty($date) ) {
            throw new Exception('查询日期为空!');
        }
        $data = [
            'user' => $this->config['user'],
            'timestamp' => $timestamp,
            'sign' => $this->getSign($timestamp),
            'debug' => $this->config['debug'],
            'sn' => $sn,
            'date' => $date, //查询日期，格式yy-MM-dd，如：2019-08-15
        ];
        return json_decode($this->curl->getCurlInfo($url,'POST',$this->config['header'],json_encode($data)));
    }

    /**
     * 8、查询订单是否打印成功
     */
    public function queryOrderState($orderId):array
    {
        $timestamp = time();
        $url = 'https://open.xpyun.net/api/openapi/xprinter/queryOrderState';
        if ( empty($orderId) ) {
            throw new Exception('订单编号为空!');
        }
        $data = [
            'user' => $this->config['user'],
            'timestamp' => $timestamp,
            'sign' => $this->getSign($timestamp),
            'debug' => $this->config['debug'],
            'orderId' => $orderId
        ];
        return json_decode($this->curl->getCurlInfo($url,'POST',$this->config['header'],json_encode($data)));
    }

    /**
     * 7、清空待打印队列
     */
    public function delPrinterQueue($sn):array
    {
        $timestamp = time();
        $url = 'https://open.xpyun.net/api/openapi/xprinter/delPrinterQueue';
        if ( empty($snlist) ) {
            throw new Exception('设备SN为空!');
        }
        $data = [
            'user' => $this->config['user'],
            'timestamp' => $timestamp,
            'sign' => $this->getSign($timestamp),
            'debug' => $this->config['debug'],
            'sn' => $sn
        ];
        return json_decode($this->curl->getCurlInfo($url,'POST',$this->config['header'],json_encode($data)));
    }

    /**
     * 6、修改打印机信息
     */
    public function updPrinter($snlist=[]):array
    {
        $timestamp = time();
        $url = 'https://open.xpyun.net/api/openapi/xprinter/updPrinter';
        if ( empty($snlist['sn']) || empty($snlist['name']) ) {
            throw new Exception('设备SN/名称为空!');
        }
        $data = [
            'user' => $this->config['user'],
            'timestamp' => $timestamp,
            'sign' => $this->getSign($timestamp),
            'debug' => $this->config['debug'],
            'sn' => $snlist['sn'],
            'name' => $snlist['name'],
        ];
        if ( isset($snlist['cardno']) ) {
            $data['cardno'] = $snlist['cardno'];
        }
        return json_decode($this->curl->getCurlInfo($url,'POST',$this->config['header'],json_encode($data)));
    }

    /**
     * 5、批量删除打印机
     */
    public function delPrinters($snlist=[]):array
    {
        $timestamp = time();
        $url = 'https://open.xpyun.net/api/openapi/xprinter/delPrinters';
        if ( empty($snlist) ) {
            throw new Exception('设备SN为空!');
        }
        $data = [
            'user' => $this->config['user'],
            'timestamp'=> $timestamp,
            'sign' => $this->getSign($timestamp),
            'debug' => $this->config['debug'],
            'snlist' => $snlist,
        ];
        return json_decode($this->curl->getCurlInfo($url,'POST',$this->config['header'],json_encode($data)));
    }

    /**
     * 3、打印小票订单
     */
    public function print($sn,$content,$print_config=[]):array
    {
        $timestamp = time();
        $url = 'https://open.xpyun.net/api/openapi/xprinter/print';
        if ( empty($sn) ) {
            throw new Exception('设备SN为空!');
        }
        if ( empty($content) ) {
            throw new Exception('打印内容为空!');
        }
        $data = [
            'user' => $this->config['user'],
            'timestamp' => $timestamp,
            'sign' => $this->getSign($timestamp),
            'debug' => $this->config['debug'],
            'sn' => $sn,
            'content' => $content
        ];
        $data = array_merge($data,$this->print_config);
        return json_decode($this->curl->getCurlInfo($url,'POST',$this->config['header'],json_encode($data)));
    }

    /**
     * 2、设置打印机语音类型
     */
    public function setVoiceType($sn,int $voiceType=4,int $volumeLevel=3):array
    {
        $timestamp = time();
        $url = 'https://open.xpyun.net/api/openapi/xprinter/setVoiceType';
        if ( empty($sn) ) {
            throw new Exception('设备SN为空!');
        }
        $data = [
            'user' => $this->config['user'],
            'timestamp' => $timestamp,
            'sign' => $this->getSign($timestamp),
            'debug' => $this->config['debug'],
            'sn' => $sn,
            'voiceType' => $voiceType,
            'volumeLevel' => $volumeLevel
        ];
        return json_decode($this->curl->getCurlInfo($url,'POST',$this->config['header'],json_encode($data)));
    }

    /**
     * 添加打印机到开发者账户（可批量）
     */
    public function addPrinters($items=[]):array
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
        return json_decode($this->curl->getCurlInfo($url,'POST',$this->config['header'],json_encode($data)));
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
