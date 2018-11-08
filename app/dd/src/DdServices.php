<?php

namespace app\dd\src;

use Config;
use Log;
use Upadd\Swoole\TcpServer;

use app\dd\src\Help;
use app\dd\src\DdConfig;
use app\dd\src\lib\Encryptor;
use app\dd\src\Parse;

use Swoole\Client as swoole_client;


class DdServices extends TcpServer
{
    use Parse;

    //流量
    protected $traffic;

    //本地地址
    public $local_server_addreer = '0.0.0.0';

    //本地端口
    public $local_port = 1987;

    //测试使用的加密端口
    public $method = 'aes-256-cfb';

    //启动tcp和udp端口监听
    public $startTcpServer = [];

    /**
     * 配置文件
     * @return mixed
     */
    public function configure()
    {
        $config = Config::get('dd@dd_server');
        $config['daemonize'] = Config::get('dd@daemonize');
        return $config;
    }

    /**
     * @var bool
     */
    private $isPortPasswdConfig = false;

    /**
     * @param array $config
     */
    public function setPortPasswdConfig(array $config)
    {
        $this->startTcpServer = $config;
        $this->isPortPasswdConfig = true;
    }

    /**
     * Set up monitor port
     * @return mixed|void
     */
    public function doListen()
    {
        if ($this->isPortPasswdConfig == false) {
            $config = Config::get('dd@serverConfig');
            if ($config) {
                $this->startTcpServer = $config;
            }
        }

        if ($this->startTcpServer) {
            foreach ($this->startTcpServer as $k => $v) {
                //启动TCP
                $this->server->addlistener($this->local_server_addreer, $k, SWOOLE_SOCK_TCP);
                //启动UDP
                $this->server->addlistener($this->local_server_addreer, $k, SWOOLE_SOCK_UDP);
            }
        }
    }

    public function onWorkerStart($serv, $worker_id)
    {
        //每6小时清空一次dns缓存
        swoole_timer_tick(21600000, function () {
            swoole_clear_dns_cache();
        });
    }


    //缓冲区控制
    public function onBufferFull($serv, $fd)
    {
        $this->clientList[$fd]['overflowed'] = true;
        Log::cmd("server is overflowed connection[fd=$fd]");
    }

    public function onBufferEmpty($serv, $fd)
    {
        $this->clientList[$fd]['overflowed'] = false;
    }


    /**
     * 关闭
     * @param $serv
     * @param $fd
     * @param $from_id
     */
    public function onClose($serv, $fd, $from_id)
    {
        //清理掉后端连接
        if (isset($this->clientList[$fd])) {
            unset($this->clientList[$fd]);
        }
        //关闭通道的日志,包含用户使用
        Log::cmd("@@@@@@@@@@@@@@@@@@fd {$fd} closed @@@@@@@@@@@@@@@@@@@@");

        
    }

    /**
     * 链接通道
     * @param $serv
     * @param $fd
     */
    public function onConnect($serv, $fd)
    {
        $clientsInfo = $serv->connection_info($fd);
        //打开链接通道信息
//        Log::cmd("================ \n\r
//            fd={$fd} \n\r
//            new clinet ip={$clientsInfo['remote_ip']} \n\r
//            port: {$clientsInfo['remote_port']} \n\r
//        ===============");
//
//        Log::cmd("onConnect info ReactorThreadID:{$clientsInfo['reactor_id']} \n\r
//            socketPort={$clientsInfo['server_fd']} \n\r
//            server monitor port: {$clientsInfo['server_port']} \n\r
//            clinet port: {$clientsInfo['remote_port']} \n\r
//            clinet ip: {$clientsInfo['remote_ip']} \n\r
//            the client connect server time: {$clientsInfo['connect_time']} \n\r
//            Last time received data: {$clientsInfo['last_time']}"
//        );

        $server_port = $clientsInfo['server_port'];
        //判断通信端口是否正确
        if (array_key_exists($server_port, $this->startTcpServer)) {

            // 设置当前连接的状态为 STAGE_INIT ，初始状态
            if (!isset($this->clientList[$fd])) {
                $this->clientList[$fd]['stage'] = DdConfig::STAGE_INIT;
            }

            //server_port
            $this->clientList[$fd]['info'] = $clientsInfo;

            $clientsInfoPasswd = $this->startTcpServer[$server_port];
            //标记哪个用户
            $this->clientList[$fd]['info']['memberPasswd'] = $clientsInfoPasswd;
            //server_port
            $this->clientList[$fd]['encryptor'] = new Encryptor($clientsInfoPasswd, $this->method);
            //初始化各属性
            $this->clientList[$fd]['splQueue'] = new \SplQueue();
            //判断缓冲区是否已满
            $this->clientList[$fd]['overflowed'] = false;
            $this->clientList[$fd]['ota_enable'] = false;
            $this->clientList[$fd]['_ota_chunk_idx'] = 0;
            $this->clientList[$fd]['_ota_len'] = 0;
            $this->clientList[$fd]['_ota_buff_head'] = b"";
            $this->clientList[$fd]['_ota_buff_data'] = b"";
        } else {
            Log::cmd("onConnect error {$fd} to Validate against {$clientsInfo['server_port']}");
            return;
        }
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

        if (array_key_exists($fd, $this->clientList) == false) {
            Log::cmd("onReceive error connect {$fd}");
            $this->server->close($fd, true);
            return;
        } else {

//            //客户端ip和端口
//            $remote_port = $this->clientList[$fd]['info']['remote_port'];
//            $remote_ip = $this->clientList[$fd]['info']['remote_ip'];
//            //服务端监听端口
//            $server_port = $this->clientList[$fd]['info']['server_port'];
//            $memberPasswd = $this->clientList[$fd]['info']['memberPasswd'];
//
//            //标记状态为假
//            $this->traffic->is_visit = false;
//            //统计数据
//            $this->traffic->clientInfo = [
//                'memberPasswd' => $memberPasswd,
//                'server_port' => $server_port,
//                'remote_port' => $remote_port,
//                'remote_ip' => $remote_ip,
//            ];
//
//            $this->traffic->clinet = $memberPasswd;

            // 先解密数据
            $data = $this->clientList[$fd]['encryptor']->decrypt($data);
//            $this->pushClinet($this->server, $fd, $from_id, $data);
            $server = $this->server;
            go(function () use ($server,$fd, $from_id, $data) {
                $this->pushClinet($server, $fd, $from_id, $data);
            });
        }
    }


    /**
     * @param $serv
     * @param $fd
     * @param $from_id
     * @param $data
     * @return mixed
     */
    public function pushClinet($serv, $fd, $from_id, $data)
    {
        switch ($this->clientList[$fd]['stage']) {
            // 如果不是STAGE_STREAM，则尝试解析实际的请求地址及端口
            case DdConfig::STAGE_INIT:

            case DdConfig::STAGE_ADDR:
                // 解析ParsingSocket5头
                $header = Help::socket5Header($data, $this->ota_enable);
                // 解析头部出错，则关闭连接
                if (!$header) {
                    Log::cmd("If the header error is resolved, the connection is closed. @Line" . __LINE__);
                    return $serv->close($fd);
                }

                $this->doTcpPush($serv, $fd, $from_id, $data, $header);
                break;

            case DdConfig::STAGE_CONNECTING:
                $this->clientList[$fd]['splQueue']->push($data);
                break;

            case DdConfig::STAGE_STREAM:
                if (isset($this->clientList[$fd]['clientSocket'])) {
                    $this->writeToSock($fd, $data);
                }
                break;

            default:
                Log::cmd(" Headers the Parsing failure {$fd} @Line:" . __LINE__);
                break;
        }
    }


    /**
     * @param $serv
     * @param $data
     * @param $clientInfo
     */
    public function onPacket($serv, $data, $clientInfo)
    {
        Log::cmd("=============== new UDP =======================");

        //计算当前server数据
        $info = $serv->getClientInfo(ip2long($clientInfo['address']), ($clientInfo['server_socket'] << 16) + $clientInfo['port']);
        $server_port = $info['server_port'];
        //获取用户加密密码
        $clientsInfoPasswd = $this->startTcpServer[$server_port];

        $encryptor = new Encryptor($clientsInfoPasswd, $this->method);

//        Log::cmd"UDP,server_socket :{$clientInfo['server_socket']}");
        $data = $encryptor->decrypt($data);
        if (!$data) {
            Log::cmd("UDP handle_server: data is empty after decrypt server port:{$server_port}");
            return;
        }
        $header = Help::socket5Header($data, $this->ota_enable);
//        $header[] = 'UDP====================';
//        $this->traffic->clientInfo['info'] = json_encode($header);
        //启动计算
//        $this->traffic->main();
        // 解析头部出错，则关闭连接
        if (!$header) {
            Log::cmd("parse UDP header error maybe wrong password {$clientInfo['address']}:{$clientInfo['port']} server port:{$server_port}");
            return;
        }

        //addrtype, dest_addr, dest_port, header_length, ota_enable= header_result
        $clientSocket = new swoole_client(SWOOLE_SOCK_UDP, SWOOLE_SOCK_ASYNC);
        //头部长度
        $header_len = $header[3];
        if ($header[4]) {
            if (strlen($data) < ($header_len + DdConfig::ONETIMEAUTH_BYTES)) {
                Log::cmd("UDP OTA header is too short server port:{$server_port}");
                return;
            }
            $_hash = substr($data, -DdConfig::ONETIMEAUTH_BYTES);
            $data = substr($data, 0, -DdConfig::ONETIMEAUTH_BYTES);

            $_key = $encryptor->_key;
            $key = $encryptor->_decipherIv . $_key;
            //验证OTA 头部hash值
            $gen = Help::hashAuthGen($data, $key);
            if ($gen != $_hash) {
                Log::cmd("UDP OTA header fail  server port:{$server_port}");
                return;
            }
        }
        $clientSocket->on('connect', function (swoole_client $cUdp) use ($data, $header_len) {
            if (strlen($data) > $header_len) {
                $cUdp->send(substr($data, $header_len));
            }
            #$res = $client->recv();
        });


        $clientSocket->on('receive', function (swoole_client $cUdp, $_data) use ($serv, $clientInfo, $header, $encryptor) {
            //$errCode = $serv->getLastError(); if (1008 == $errCode)//缓存区已满
            //先判断 send或者 push的返回是否为false, 然后调用 getLastError得到错误码，进行对应的处理逻辑
            try {
                $_header = Help::packHeader($header[1], $header[0], $header[2]);
                $_data = $encryptor->encrypt($_header . $_data);
                $serv->sendto($clientInfo['address'], $clientInfo['port'], $_data, $clientInfo['server_socket']);
            } catch (Exception $e) {
                //var_dump($e);
            }
        });

        if ($header[0] == DdConfig::ADDRTYPE_HOST) {
            swoole_async_dns_lookup($header[1], function ($host, $ip) use (&$header, $clientSocket, $clientInfo, $server_port) {
                $ota = $header[4] ? 'OTA' : '';
                Log::cmd(
                    "UDP {$ota} connecting {$host}:{$header[2]} from {$clientInfo['address']}:{$clientInfo['port']} server port:{$server_port}"
                );
                $header[1] = $ip;
                $clientSocket->connect($ip, $header[2]);
            });
        } elseif ($header[0] == DdConfig::ADDRTYPE_IPV4) {
            $ota = $header[4] ? 'OTA' : '';
            Log::cmd(
                "UDP {$ota} connecting {$header[1]}:{$header[2]} from {$clientInfo['address']}:{$clientInfo['port']} server port:{$server_port}"
            );
            $clientSocket->connect($header[1], $header[2]);
        } else {
            Log::cmd(" Dns Parsing failure (UDP) @Line:" . __LINE__);
        }

    }


}