<?php

namespace console\action;

use Data;
use Config;
use Log;
use app\dd\src\DdServices;
use app\dd\src\DdLocaService;

use tool\Help as th;


class DdAction extends \Upadd\Frame\Action
{

    /**
     *
     * php console.php --u=dd --p=server
     * php console.php --u=dd --p=server --pp=base64_encode(json_encode($array))
     * @pp in param array['port'=>passwd]
     * php console.php --u=dd --p=server --proxy=off
     * @throws \Upadd\Bin\UpaddException
     */
    public function server()
    {
        $is_poroxy = Data::get('proxy', 'on');
        $portPasswd = Data::get('pp');
        if (!empty($portPasswd)) {
            $portPasswd = th::baseJsonToArray($portPasswd);
        }
        $ddServer = DdServices::create('upadd_dd_server', 'tcp://127.0.0.1:6688');
        if (is_array($portPasswd)) {
            $ddServer->setPortPasswdConfig($portPasswd);
        }
        if ($is_poroxy == 'off') {
            $ddServer->offProxy();
        }
        $ddServer->start();
    }


    /**
     * php console.php --u=dd --p=local
     * php console.php --u=dd --p=local --config=eyJzZXJ2ZXIiOiIxMDYuMTQuMTQ3LjE2OSIsInNlcnZlcl9wb3J0Ijo5MDAxLCJwYXNzd29yZCI6IiFAIUAjIzkwOTAiLCJsb2NhbF9wb3J0Ijo2NjZ9
     * @throws \Upadd\Bin\UpaddException
     */
    public function local()
    {
        $conf = Data::get('config');
        if ($conf != null) {
            $conf = json(base64_decode($conf));
        }

        //本地配置
        $config = Config::get('dd@local_config');
        if (is_array($conf) && !empty($conf)) {
            $config = array_merge($config, $conf);
        }
        $local = DdLocaService::create('upadd_dd_local', 'tcp://0.0.0.0:6666');
        $local->getConfig($config);
        $local->start();
    }


    /**
     * php console.php --u=dd --p=test
     */
    public function test()
    {
//        $conf = [
//            'server' => '106.14.147.169',
//            'server_port' => 9001,
//            'password' => '!@!@##9090',
//            'local_port' => 6666,
//        ];
//        $json = base64_encode(json($conf));
//        echo "php console.php --u=dd --p=local --config={$json}" . "\n\r";

        $conf = [
            443=>'!@!@##9090',
            80=>'!@!@##9191'
        ];
        $json = base64_encode(json($conf));
        echo "php console.php --u=dd --p=server --pp={$json}" . "\n\r";
    }


}