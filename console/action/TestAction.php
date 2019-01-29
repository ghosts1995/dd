<?php

namespace console\action;

use Data;
use Config;
use Log;

use tool\HttpClinet;
use services\ss\Help;

use app\process\ProxyProcess;


class TestAction extends \Upadd\Frame\Action
{

    /**
     * php console.php --u=test --p=proxy
     */
    public function proxy()
    {
        $requests = [
//            'http://www.baidu.com',
            'http://zmq.cc/ip.php',
//            'http://ip-api.org',
        ];

        $main = curl_multi_init();
        $results =  $errors =  $info = [];

        $count = count($requests);
        for ($i = 0; $i < $count; $i++) {
            $handles[$i] = curl_init($requests[$i]);
            var_dump($requests[$i]);
            curl_setopt($handles[$i], CURLOPT_URL, $requests[$i]);
            curl_setopt($handles[$i], CURLOPT_RETURNTRANSFER, 1);
            //代理
            curl_setopt($handles[$i], CURLOPT_HTTPPROXYTUNNEL, true);
            curl_setopt($handles[$i], CURLOPT_PROXYAUTH, CURLAUTH_BASIC);
//            curl_setopt($handles[$i], CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
            curl_setopt($handles[$i], CURLOPT_PROXY, '39.137.168.230');
            curl_setopt($handles[$i], CURLOPT_PROXYPORT, '8080');
            //压缩并发
            curl_multi_add_handle($main, $handles[$i]);
        }
        $running = 0;
        do {
            curl_multi_exec($main, $running);
        } while ($running > 0);
        for ($i = 0; $i < $count; $i++) {
            $results[] = curl_multi_getcontent($handles[$i]);
            $errors[] = curl_error($handles[$i]);
            $info[] = curl_getinfo($handles[$i]);
            curl_multi_remove_handle($main, $handles[$i]);
        }
        curl_multi_close($main);
        print_r($results);
//        var_dump($errors);
//        var_dump($info);
    }


}