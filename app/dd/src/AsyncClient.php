<?php

namespace app\dd\src;

use Log;
use app\dd\src\DdConfig;
use app\dd\src\Help;

use Swoole\Client as swoole_client;

class AsyncClient
{

    private $clientList;

    //ota加密类型是否开启,属于头检测
    public $ota_enable = false;

    private $target_client_handle;
    private $serv;
    private $fd;
    private $from_id;
    private $data;
    private $header;


    public static function init($clientList, $is_socketPorxy)
    {
        return new static($clientList, $is_socketPorxy);
    }

    public function __construct($clientList, $is_socketPorxy)
    {
        $this->target_client_handle = new swoole_client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_ASYNC);
        $this->target_client_handle->closing = false;
        $this->clientList = $clientList;
        if ($is_socketPorxy) {
            $proxy = Help::getProxy();
            if ($proxy) {
                $this->target_client_handle->set($proxy);
            }
        }
        $this->_target_client_handle->on('connect', array($this, 'connect'));
        $this->_target_client_handle->on('error', array($this, 'error'));
        $this->_target_client_handle->on('close', array($this, 'close'));
        $this->_target_client_handle->on('receive', array($this, 'receive'));
    }

    public function connect(swoole_client $target_server_handle)
    {
        $this->clientList[$fd]['clientSocket'] = $cli;
        // shadowsocks客户端第一次发来的数据超过头部，则要把头部后面的数据发给远程服务端
        if (strlen($data) > $header_len) {
            $this->writeToSock($fd, substr($data, $header_len));
        }

        $count = isset($this->clientList[$fd]['splQueue']) ? count($this->clientList[$fd]['splQueue']) : 0;
        for ($i = 0; $i < $count; $i++) {//读取队列
            $v = $this->clientList[$fd]['splQueue']->shift();
            $this->writeToSock($fd, $v);
        }
        $this->clientList[$fd]['stage'] = DdConfig::STAGE_STREAM;
    }


}