<?php

namespace app\dd\src;

use Log;
use app\dd\src\DdConfig;
use app\dd\src\Help;

use Swoole\Client as swoole_client;
//use Swoole\Coroutine\Client as swoole_client;


trait Parse
{
    /**
     * 客户端列表
     * @var
     */
    public $clientList;

    //ota加密类型是否开启,属于头检测
    public $ota_enable = false;

    /**
     * dd OTA 功能拆包部分 by @Zac
     * @param $fd
     * @param $data
     */
    protected function otaChunkData($fd, $data)
    {
        //tcp 是流式传输，接收到的数据包可能不是一个完整的chunk 不能以strlen来判断长度然后直接return
        $server_port = $this->clientList[$fd]['info']['server_port'];
        while (strlen($data) > 0) {
            if ($this->clientList[$fd]['_ota_len'] == 0) {
                $_ota_buff_head_len = strlen($this->clientList[$fd]['_ota_buff_head']);    //已缓存的头部长度
                $left_ota_buff_head = DdConfig::ONETIMEAUTH_CHUNK_BYTES - $_ota_buff_head_len;    //还需缓存的头部长度
                $this->clientList[$fd]['_ota_buff_head'] .= substr($data, 0, $left_ota_buff_head);
                $data = substr($data, $left_ota_buff_head);
                //缓存到规定长度后开始解析头部
                if (strlen($this->clientList[$fd]['_ota_buff_head']) === DdConfig::ONETIMEAUTH_CHUNK_BYTES) {
                    $data_len = substr($this->clientList[$fd]['_ota_buff_head'], 0, DdConfig::ONETIMEAUTH_CHUNK_DATA_LEN);
                    //理论上一个完整OTA加密包的长度
                    $this->clientList[$fd]['_ota_len'] = unpack('n', $data_len)[1];
                }
            }

            $buffed_data_len = strlen($this->clientList[$fd]['_ota_buff_data']);//已获取数据长度
            $left_buffed_data_len = $this->clientList[$fd]['_ota_len'] - $buffed_data_len;//还应该获取的数据长度

            $this->clientList[$fd]['_ota_buff_data'] .= substr($data, 0, $left_buffed_data_len);
            $data = substr($data, $left_buffed_data_len);
            //接收到了一个完整的包，开始OTA包校验
            if (strlen($this->clientList[$fd]['_ota_buff_data']) == $this->clientList[$fd]['_ota_len']) {
                $_hash = substr($this->clientList[$fd]['_ota_buff_head'], DdConfig::ONETIMEAUTH_CHUNK_DATA_LEN);

                $_data = $this->clientList[$fd]['_ota_buff_data'];
                $index = pack('N', $this->clientList[$fd]['_ota_chunk_idx']);
                $key = $this->clientList[$fd]['encryptor']->_decipherIv . $index;
                $gen = Help::hashAuthGen($_data, $key);
                if ($gen == $_hash) {
                    //将当前通过校验的数据包转发出去,同时编号+1
                    $this->clientList[$fd]['clientSocket']->send($this->clientList[$fd]['_ota_buff_data']);
                    $this->clientList[$fd]['_ota_chunk_idx'] += 1;
                    Log::cmd("TCP OTA chunk ok ok ok ok! server port:{$server_port}");
                } else {
                    Log::cmd("TCP OTA fail, drop chunk ! server port:{$server_port}");
                }
                $this->clientList[$fd]['_ota_buff_head'] = b"";
                $this->clientList[$fd]['_ota_buff_data'] = b"";
                $this->clientList[$fd]['_ota_len'] = 0;
            }
        }
    }


    /**
     * 写入通道
     * @param $fd
     * @param $data
     */
    protected function writeToSock($fd, $data)
    {
        if ($this->clientList[$fd]['ota_enable']) {
            $this->otaChunkData($fd, $data);
        } else {
            if (isset($this->clientList[$fd]['clientSocket'])) {
                $this->clientList[$fd]['clientSocket']->send($data);
            } else {
                Log::cmd("writeToSock fail ");
            }
        }
    }


    public $is_socketPorxy = false;

    public function offProxy()
    {
        $this->is_socketPorxy = true;
    }

    /**
     * @param $serv
     * @param $fd
     * @param $from_id
     * @param $data
     * @param $header
     * @return mixed
     */
    public function doTcpPush($serv, $fd, $from_id, $data, $header)
    {
        //头部长度
        $header_len = $header[3];
        $this->clientList[$fd]['ota_enable'] = $header[4];

        //头部OTA判断
        if ($this->clientList[$fd]['ota_enable']) {

            if (strlen($data) < ($header_len + DdConfig::ONETIMEAUTH_BYTES)) {
                Log::cmd("TCP OTA header is too short server");
                return $serv->close($fd, true);
            }

            //$offset	= $header_len + ONETIMEAUTH_BYTES;
            //客户端发过来的头部hash值
            $_hash = substr($data, $header_len, DdConfig::ONETIMEAUTH_BYTES);
            $pushData = substr($data, 0, $header_len);

            $_key = $this->clientList[$fd]['encryptor']->_key;
            $key = $this->clientList[$fd]['encryptor']->_decipherIv . $_key;
            //验证OTA 头部hash值
            $gen = Help::hashAuthGen($pushData, $key);
            if ($gen != $_hash) {
                Log::cmd(" TCP OTA header fail.");
                return $serv->close($fd, true);
            }
            $header_len += DdConfig::ONETIMEAUTH_BYTES;
            //数据部分OTA判断
            //$data = substr($data,$header_len);
        }

        /**
         * 判断客户端信息
         */
        if (!array_key_exists($fd, $this->clientList)) {
            Log::cmd("Lack {$fd} @LINE" . __LINE__);
        }

        if (array_key_exists('clientSocket', $this->clientList[$fd]) == false) {
            $this->clientList[$fd]['stage'] = DdConfig::STAGE_CONNECTING;
            $clientSocket = new swoole_client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_ASYNC);
            $clientSocket->closing = false;

            if ($this->is_socketPorxy) {
                $proxy = Help::getProxy();
                if ($proxy) {
                    $clientSocket->set($proxy);
                }
            }

            $clientSocket->on('connect', function (swoole_client $cli) use ($data, $fd, $header_len) {
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
            });

            $clientSocket->on('error', function (swoole_client $cli) use ($fd, $serv) {
                $serv->close($fd, true);
            });

            $clientSocket->on('close', function (swoole_client $cli) use ($fd, $serv) {
                if (!$cli->closing) {
                    $cli->closing = true;
                    $serv->close($fd, true);
                }

                if (isset($this->clientList[$fd])) {
                    unset($this->clientList[$fd]);
                }
                Log::cmd("client {$fd} closed memory_get_usage:" . memory_get_usage());
            });


            $clientSocket->on('receive', function (swoole_client $cli, $pushData) use ($fd, $header, $serv) {
                if (isset($this->clientList[$fd]['encryptor'])) {
                    $pushData = $this->clientList[$fd]['encryptor']->encrypt($pushData);
                    if (isset($this->clientList[$fd]['overflowed']) && $this->clientList[$fd]['overflowed'] == false) {
                        $res = $serv->send($fd, $pushData);
                        if (!$res) {
                            $errCode = $serv->getLastError();
                            if (1008 == $errCode) {
                                //The cache is full.
                            } else {
                                Log::cmd("send uncatched errCode:$errCode");
                            }
                        } else {
                            //统计
                        }
                    }
                } else {
                    Log::cmd("receive error this->clientList[fd]['encryptor'] ");
                }
            });


            $this->asyncDns($fd, $header, $clientSocket);
        } else {
            Log::cmd(" ============= \r\n ============= \r\n ============= \r\n " . __LINE__);
        }

    }


    /**
     * Asynchronous DNS parsing
     */
    public function asyncDns($fd, $header, $cli)
    {
//        Log::cmd("===========================asyncDns=======================================");
//        print_r($this->toHeader);
        if ($header[0] == DdConfig::ADDRTYPE_HOST) {
            swoole_async_dns_lookup($header[1], function ($host, $ip) use ($fd, $header, $cli) {
                print_r($header);
                $server_port = $this->clientList[$fd]['info']['server_port'];
                if ($ip && 0 < $header[2] && $server_port) {
                    $cli->connect($ip, $header[2]);
                }
                $this->clientList[$fd]['stage'] = DdConfig::STAGE_CONNECTING;
                Log::cmd("reqPort:{$server_port} - ip:{$ip}:{$header[2]} @line:" . __LINE__);
            });
        } elseif ($header[0] == DdConfig::ADDRTYPE_IPV4) {
            $cli->connect($header[1], $header[2]);
            $this->clientList[$fd]['stage'] = DdConfig::STAGE_CONNECTING;
        } else {
            Log::cmd(" Dns Parsing failure {$fd} @Line:" . __LINE__);
        }
    }

}