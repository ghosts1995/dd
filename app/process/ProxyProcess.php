<?php

namespace app\process;

use Data;
use Config;
use Log;
use Swoole\Process as swoole_process;

use tool\GuidBuilder;
use tool\HttpClinet;

use app\model\ProxyPoolModel;

class ProxyProcess extends \app\process\BaseProcess
{

    public function dowProxy($worker)
    {
        // 注册监听管道的事件，接收任务
        swoole_event_add($worker->pipe, function ($pipe) use ($worker) {
            $data = $worker->read();
//            Log::cmd("====pid:" . $worker->pid . ": " . "data:{$data}");
            if ($data == 'exit') {
                // 收到退出指令，关闭子进程
                $worker->exit();
                exit;
            }
            $this->setProxyData();

            // 模拟任务执行过程
//            sleep(mt_rand(1, 2));
            // 执行完成，通知父进程
            $worker->write("" . $worker->pid);
        });
    }


    /**
     *
     */
    public function checkProxy($worker)
    {
        // 注册监听管道的事件，接收任务
        swoole_event_add($worker->pipe, function ($pipe) use ($worker) {
            $data = $worker->read();
//            Log::cmd("====pid:" . $worker->pid . ": " . "data:{$data}");
            if ($data == 'exit') {
                // 收到退出指令，关闭子进程
                $worker->exit();
                exit;
            }

            $this->checkOut();
            // 模拟任务执行过程
//            sleep(mt_rand(1, 5));
            // 执行完成，通知父进程
            $worker->write("" . $worker->pid);
        });
    }


    private function checkTcp($ip, $port)
    {
        $sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        socket_set_nonblock($sock);
        socket_connect($sock, $ip, $port);
        socket_set_block($sock);
        $r = array($sock);
        $w = array($sock);
        $f = array($sock);
        $tcpStatus = socket_select($r, $w, $f, 5);
        if ((int)$tcpStatus == 1) {
            unset($tcpStatus);
            socket_close($sock);
            return true;
        } else {
            return false;
        }
    }


    /**
     * @return array|bool
     */
    private function request()
    {
        //toinfo32156@yopmail.com
        $http = new HttpClinet();
        $http->url = 'https://proxyapi.mimvp.com/api/fetchsecret.php?orderid=861000618431501505&num=5&http_type=5&result_fields=1,2,3&result_format=json';
        $response = $http->getResponse();
        if ((int)$response['code'] === 0) {
            if (count($response['result']) >= 1) {
                $result = $response['result'];
                if ($result) {
                    $proxy = [];
                    foreach ($result as $k => $v) {
                        $tmp = [];
                        if (isset($v['ip:port'])) {
                            $proxyData = explode(':', $v['ip:port']);
                            if ($proxyData) {
                                $tmp['guid'] = GuidBuilder::create();
                                $tmp['type'] = 'mimvp';
                                $tmp['mode'] = 1;
                                $tmp['ip'] = $proxyData[0];
                                $tmp['port'] = $proxyData[1];
                                $tmp['ip_md5'] = md5($v['ip:port']);
                                $tmp['user'] = 'b904554f9ffb';
                                $tmp['passwd'] = '0c6aa01865';
                                $tmp['is_user'] = 2;
                                $tmp['is_delete'] = 1;
                                $tmp['update_time'] = time();
                                $tmp['add_time'] = time();
                                $proxy[] = $tmp;
                            }
                        }
                    }
                    return $proxy;
                }
            }
        }
        return false;
    }


    /**
     *
     */
    private function setProxyData()
    {
        $newProxy = $this->request();
        if ($newProxy) {
            //ProxyPoolModel
            foreach ($newProxy as $k => $v) {
                Log::cmd("check data({$k}): {$v['ip']}:{$v['port']}");
                $isMd5 = ProxyPoolModel::byIpMd5One($v['ip_md5']);
                if (!$isMd5) {
                    ProxyPoolModel::add($v);
                }
            }
        } else {
            Log::cmd("setProxyData:not data");
        }
    }

    public function test()
    {
//        $this->setProxyData();
        $this->checkOut();
    }


    private function checkCurl($info)
    {
        $req = new HttpClinet();
        if($info['is_user'] == 2){
            $req->setProxy($info['ip'], $info['port'], true, $info['user'], $info['passwd']);
        }
        $req->url = 'http://65.49.226.175:9191/reverse/clinet/get/ip?type=json';
        if ($req->getResponse() == false) {
            return true;
        } else {
            return false;
        }
    }


    /**
     *
     */
    private function checkOut()
    {
        $proxyData = ProxyPoolModel::getProxyList();
        if ($proxyData) {
            $result = $proxyData;
            $count = count($result);
            $this->write("========count {$count} =====");
            if ($count >= 1) {
                foreach ($result as $k => $v) {
                    //检测通道是否正常
                    $checkTcp = $this->checkCurl($v);
                    if ($checkTcp) {
                        $this->write("fail check data({$k}): {$v['ip']}:{$v['port']}");
                        ProxyPoolModel::update([
                            'is_delete' => 2,
                            'delete_time' => time(),
                            'update_time' => time(),
                        ], ['id' => $v['id']]);
                    } else {
                        $this->write("check open {$v['ip']}:{$v['port']}");
                    }
                }
            } else {
                $this->write("checkOut:not data");
            }
        } else {
            $this->write("checkOut:fail");
        }
    }


    /**
     * @param $info
     */
    private function write($info)
    {
//        $now = date('Y-m-d H:i:s', time());
//        $data = '[' . $now . '] ' . $info . PHP_EOL;
//        file_put_contents('checkProxy.logs', $data . "\n", FILE_APPEND);
//        echo '[' . $now . '] ' . $info . PHP_EOL;
//        unset($data);
        Log::cmd($info);
    }

}
