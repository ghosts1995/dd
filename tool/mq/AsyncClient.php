<?php

namespace tool\mq;

use Swoole\Client as swoole_client;
use Log;
use tool\mq\Unpack;

class AsyncClient
{

    private $target_client_handle;

    private $serverTyep = 'TCP';

    private $is_setConfig = false;

    private $config = [];

    private $host = '127.0.0.1';

    private $port = 6899;

    private $data = [];

    /**
     * @var string
     */
    private $mqTyep = '';

    /**
     * @param array $data
     * @param string $host
     * @param int $port
     * @return AsyncClient
     */
    public static function init($data = [], $host = '', $port = 0)
    {
        Log::cmd("init");
        return new static($data, $host, $port);
    }


    /**
     * AsyncClient constructor.
     * @param $host
     * @param $port
     */
    final private function __construct($data = [], $host = '', $port = 0)
    {
        if ($host && $port) {
            $this->host = $host;
            $this->port = $port;
        }
        $this->data = $data;
        if ($this->serverTyep == 'TCP') {
            $this->target_client_handle = new swoole_client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_ASYNC);
        }

        if ($this->is_setConfig) {
            $this->target_client_handle->set($this->config);
        }
        $this->target_client_handle->on('Connect', array($this, 'onConnect'));
        $this->target_client_handle->on('Receive', array($this, 'onReceive'));
        $this->target_client_handle->on('Error', array($this, 'onError'));
        $this->target_client_handle->on('Close', array($this, 'onClose'));
        $this->target_client_handle->on('BufferEmpty', array($this, 'onBufferFull'));
        $this->target_client_handle->on('BufferEmpty', array($this, 'onBufferEmpty'));
    }


    //缓冲区控制
    public function onBufferFull($cli)
    {
        Log::cmd("onBufferFull");
    }

    public function onBufferEmpty($cli)
    {
        Log::cmd("onBufferEmpty");
    }

    public function onError($cli)
    {
        Log::cmd("onError @LINE" . __LINE__);
    }


    public function onClose($cli)
    {
        Log::cmd("onClose closed memory_get_usage:" . memory_get_usage());
    }


    public function onConnect($cli)
    {
        Log::cmd("ip:{$this->host},port:$this->port");
        $data = $this->msg($this->data);
        Log::cmd($data);
        $this->target_client_handle->send($data);
//        $this->target_client_handle->close();
    }


    public function push()
    {
        $fp = $this->target_client_handle->connect($this->host, $this->port, 1);
        if ($fp) {
            Log::cmd("push ok");
        } else {
            Log::cmd("not push");
        }
    }

    /**
     * @param swoole_client $target_server_handle
     * @param $serverData
     */
    public function onReceive($cli, $serverData)
    {
        $serverData = Unpack::decode($serverData);
        print_r($serverData);
        Log::cmd("onReceive");
        $this->target_client_handle->close();
    }


    /**
     * @param string $type
     */
    public function setMqType($type = 'set')
    {
        $this->mqType = $type;
        return $this;
    }

    /**
     * @param array $config
     * @return bool
     */
    public function setConfig(array $config = []): bool
    {
        $this->is_setConfig = true;
        $this->config = $config;
        return true;
    }


    /**
     * @param string $type
     * @param array $data
     * @return string
     */
    private function msg($data = [])
    {
        return Unpack::encode([
            'type' => $this->mqType,
            'data' => $data
        ]);
    }


    public static function test()
    {

        $client = new swoole_client(SWOOLE_SOCK_TCP);
//
//        $client->on("connect", function ($cli) {
//            $cli->send(Unpack::encode([
//                'type' => 'get',
//                'data' => []
//            ]));
//            $cli->close();
//        });
//
//        $client->on("receive", function ($cli, $data) {
//            echo "Received: " . $data . "\n";
//            $cli->close();
//        });
//        $client->on("error", function ($cli) {
//            echo "Connect failed\n";
//        });
//        $client->on("close", function ($cli) {
//            echo "Connection close\n";
//        });

        $client->connect('172.11.22.17', 6899, 1);
//        $this->mqType = 'get';
        $client->send(Unpack::encode([
            'type' => 'get',
            'data' => []
        ]));
        $msg = $client->recv();
        if ($msg) {
            print_r(Unpack::decode($msg));
            $client->close();
        }
    }

}