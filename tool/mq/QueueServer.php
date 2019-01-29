<?php

namespace tool\mq;

use SplQueue;

use Config;
use Log;
use Upadd\Swoole\TaskServer;

use tool\mq\Unpack;

class QueueServer extends TaskServer
{

    private $spl;

    /**
     * 配置文件
     * @return mixed
     */
    public function configure()
    {
        $config = Config::get('mq@serverConfig');
        $config['daemonize'] = Config::get('mq@daemonize');
        return $config;
    }


    public function onWorkerStart($serv, $worker_id)
    {
        $this->spl = new SplQueue();
//        print_r($this->spl);
    }

    /**
     * Set up monitor port
     * @return mixed|void
     */
    public function doListen()
    {
//        $this->server->addlistener('127.0.0.1', 6899, SWOOLE_SOCK_TCP);
//        //启动UDP
//        $this->server->addlistener('127.0.0.1', 6899, SWOOLE_SOCK_UDP);
    }


    public function set($data)
    {
        if ($this->spl->push($data)) {
            return true;
        } else {
            return false;
        }
    }

    public function get()
    {
        $data = $this->spl->shift();
        echo count($this->spl); // 队列长度
        var_export($this->spl); // SplQueue::__set_state(array())
        if ($data) {
            return $data;
        } else {
            return null;
        }
    }

    /**
     * @param $fd
     * @return mixed
     */
    private function toClose($fd)
    {
        return $this->server->close($fd, true);
    }

    /**
     * 关闭
     * @param $serv
     * @param $fd
     * @param $from_id
     */
    public function onClose($serv, $fd, $from_id)
    {
        $this->toClose($fd);
        Log::cmd("closed:{$fd}");
    }

    /**
     * 链接通道
     * @param $serv
     * @param $fd
     */
    public function onConnect($_server, $fd, $from_id)
    {
        $clientsInfo = $_server->connection_info($fd);
        //打开链接通道信息
        Log::cmd("onConnect info ReactorThreadID:{$clientsInfo['reactor_id']} \n\r
            socketPort={$clientsInfo['server_fd']} \n\r
            server monitor port: {$clientsInfo['server_port']} \n\r
            clinet port: {$clientsInfo['remote_port']} \n\r
            clinet ip: {$clientsInfo['remote_ip']} \n\r
            the client connect server time: {$clientsInfo['connect_time']} \n\r
            Last time received data: {$clientsInfo['last_time']}"
        );
    }


    /**
     * @param array $param
     * @param array $client
     * @return array
     */
    public function doWork($param, $client = [])
    {
        $fd = $param['fd'];
        $results = $param['results'];

        Log::cmd("clinetEncodeData:{$results}");


        $results = Unpack::decode($results);
        print_r($results);


        if (!isset($results['type'])) {
            return $this->msg($fd, 206, '解析失败');
        }

        if ($results['type'] == 'set') {
            if ($this->set(json_decode($results['data']))) {
                return $this->msg($fd, 200, 'Team success');
            }
        } elseif ($results['type'] == 'get') {
            $data = $this->get();
            if ($data) {
                return $this->msg($fd, 200, $data);
            } else {
                return $this->msg($fd, 201, 'not data');
            }
        }
        return $this->msg($fd, 204, '系统错误');
    }


    /**
     * @param $code
     * @param $msg
     * @return string
     */
    private function msg($fd, $code, $msg)
    {
        return $this->results($fd, Unpack::encode([
            'code' => $code,
            'msg' => $msg
        ]));
    }

    /**
     * @param $serv
     * @param $data
     * @param $clientInfo
     */
    public function onPacket($serv, $data, $clientInfo)
    {

    }


}