<?php

namespace console\action;

use Data;
use Config;
use Log;

use tool\mq\QueueServer;
use tool\mq\AsyncClient;


class MqAction extends \Upadd\Frame\Action
{

    /**
     * php console.php --u=mq --p=start
     */
    public function start()
    {
        $ser = QueueServer::create('upadd_mq_server', 'tcp://0.0.0.0:6899');
        return $ser->start();
    }


    //php console.php --u=mq --p=http
    public function http()
    {
//        AsyncClient::test();
//        go(function (){
//            for ($i = 0; $i < 1000; $i++) {
//                $asy = AsyncClient::init("test:" . mt_rand(3, 9999), '172.11.22.17', 6899);
//                $asy->setMqType('set')->push();
//            }
//        });

        $asy = AsyncClient::init("test:" . mt_rand(3, 9999), '172.11.22.17', 6899);
        $asy->setMqType('get')->push();
//        for ($i = 0; $i < 1000; $i++) {
//            $asy = AsyncClient::init("test:" . mt_rand(3, 9999), '172.11.22.17', 6899);
//            $asy->setMqType('set')->push();
//        }

    }

    //php console.php --u=mq --p=test
    public function test()
    {
        $proxy = [
            ['ip' => '127.0.0.11', 'port' => 3321, 'user' => 'info1212S', 'passwd' => '33333333333'],
            ['ip' => '127.0.0.11', 'port' => 3321, 'user' => 'info1212S', 'passwd' => '33333333333'],
            ['ip' => '127.0.0.11', 'port' => 3321, 'user' => 'info1212S', 'passwd' => '33333333333'],
            ['ip' => '127.0.0.11', 'port' => 3321, 'user' => 'info1212S', 'passwd' => '33333333333'],
            ['ip' => '127.0.0.11', 'port' => 3321, 'user' => 'info1212S', 'passwd' => '33333333333'],
            ['ip' => '127.0.0.11', 'port' => 3321, 'user' => 'info1212S', 'passwd' => '33333333333'],
            ['ip' => '127.33.11.11', 'port' => 3321, 'user' => 'info1212S', 'passwd' => '33333333333'],
            ['ip' => '127.22.33.11', 'port' => 3321, 'user' => 'info1212S', 'passwd' => '33333333333'],
            ['ip' => '127.32.11.11', 'port' => 3321, 'user' => 'info1212S', 'passwd' => '33333333333'],
        ];
        $file = host() . "/data/file.qu";
        for ($i = 1; $i < count($proxy); $i++) {
            $data = \swoole_serialize::pack($proxy[mt_rand($i, 7)]);
            if ($data) {
                swoole_async_write($file, $data);
                swoole_async_read($file, function ($file, $content) {
                    $content = \swoole_serialize::unpack($content);
                    print_r($content);
                });
            }
        }


    }


}