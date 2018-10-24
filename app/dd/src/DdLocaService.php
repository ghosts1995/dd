<?php

namespace app\dd\src;

use Config;
use Log;
use Upadd\Swoole\TcpServer;

use app\dd\src\DdConfig;
use app\dd\src\lib\Encryptor;
use app\dd\src\Help;

use Swoole\Client as swoole_client;

class DdLocaService extends TcpServer
{

    // 前端
    protected $frontends;
    // 后端
    protected $backends;

    /**
     * @var
     */
    private $localConfig;

    /**
     * 配置文件
     * @return mixed
     */
    public function configure()
    {
        $config = Config::get('dd@dd_local');
        $config['daemonize'] = Config::get('dd@daemonize');
        return $config;
    }


    /**
     * @param array $conf
     */
    public function getConfig($conf = [])
    {
        $this->localConfig = [
            'local_address' => '0.0.0.0',
            'local_port' => 6666,
            'server' => '',
            'server_port' => '',
            'password' => '',
            'method' => 'aes-256-cfb'
        ];

        if (count($conf) > 1) {
            $this->localConfig = $conf;
        }

    }


    public function onConnect($serv, $fd)
    {
        // 设置当前连接的状态为STAGE_INIT，初始状态
        if (!isset($this->frontends[$fd])) {
            $this->frontends[$fd]['stage'] = DdConfig::STAGE_INIT;
        }
        // 初始化加密类
        $this->frontends[$fd]['encryptor'] = new Encryptor($this->localConfig['password'], $this->localConfig['method']);
    }


    /**
     * @param array $param
     * @param array $client
     * @return array
     */
    public function doWork($param, $client = [])
    {
        $fd = $param['fd'];
        $from_id = $param['from_id'];
        $data = $param['results'];
//        $connection_info = $client;

        switch ($this->frontends[$fd]['stage']) {
            case DdConfig::STAGE_INIT:
                //与客户端建立SOCKS5连接
                //参见: https://www.ietf.org/rfc/rfc1928.txt
                $this->server->send($fd, "\x05\x00");
                $this->frontends[$fd]['stage'] = DdConfig::STAGE_ADDR;
                break;
            case DdConfig::STAGE_ADDR:
                $cmd = ord($data[1]);
                //仅处理客户端的TCP连接请求
                if ($cmd != DdConfig::CMD_CONNECT) {
                    Log::cmd("unsupport cmd");
                    $this->server->send($fd, "\x05\x07\x00\x01");
                    return $this->server->close($fd);
                }
                $header = Help::socket5Header($data);
                if (!$header) {
                    $this->server->send($fd, "\x05\x08\x00\x01");
                    return $this->server->close($fd);
                }


                //尚未建立连接
                if (!isset($this->frontends[$fd]['socket'])) {

                    go(function () use($fd,$data) {
                        $this->frontends[$fd]['stage'] = DdConfig::STAGE_CONNECTING;
                        //连接到后台服务器
                        $socket = new \swoole_client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_ASYNC);
                        $socket->closing = false;

                        $socket->on('connect', function (\swoole_client $socket) use ($data, $fd) {
                            $this->backends[$socket->sock] = $fd;
                            $this->frontends[$fd]['socket'] = $socket;
                            $this->frontends[$fd]['stage'] = DdConfig::STAGE_STREAM;
                            $socket->send($this->frontends[$fd]['encryptor']->encrypt(substr($data, 3)));
                            // 接受代理请求
                            $buf_replies = "\x05\x00\x00\x01\x00\x00\x00\x00" . pack('n', $this->localConfig['local_port']);
                            $this->server->send($fd, $buf_replies);
                        });

                        $socket->on('error', function (\swoole_client $socket) use ($fd) {
                            Log::cmd("connect to backend server failed");
                            $this->server->send($fd, "backend server not connected. please try reconnect.");
                            $this->server->close($fd);
                        });

                        $socket->on('close', function (\swoole_client $socket) use ($fd) {
                            unset($this->backends[$socket->sock]);
                            unset($this->frontends[$fd]);
                            if (!$socket->closing) {
                                $this->server->close($fd);
                            }
                        });

                        $socket->on('receive', function (\swoole_client $socket, $_data) use ($fd) {
                            $this->server->send($fd, $this->frontends[$fd]['encryptor']->decrypt($_data));
                        });

                        $socket->connect($this->localConfig['server'], $this->localConfig['server_port']);
                    });

                }

                break;
            case DdConfig::STAGE_STREAM:
                if (isset($this->frontends[$fd]['socket'])) {
                    $this->frontends[$fd]['socket']->send($this->frontends[$fd]['encryptor']->encrypt($data));
                }
                break;
            default:
                break;
        }

    }




    public function onClose($serv, $fd, $from_id)
    {
        //清理掉后端连接
        if (isset($this->frontends[$fd]['socket'])) {
            $backend_socket = $this->frontends[$fd]['socket'];
            $backend_socket->closing = true;
            $backend_socket->close();
            unset($this->backends[$backend_socket->sock]);
            unset($this->frontends[$fd]);
        }
    }


}