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
     * php console.php --u=dd --p=local --server=111.33.18.67
     * @throws \Upadd\Bin\UpaddException
     */
    public function local()
    {
        $localIP = Data::get('ip', 0);
        $local_port = Data::get('local_port', 0);
        $server = Data::get('server');
        $server_port = Data::get('server_port', 0);
        $password = Data::get('password', 0);

        //本地配置
        $conf = Config::get('dd@local_config');
        if ($password) {
            $conf['password'] = $password;
        }
        if ($server_port) {
            $conf['server_port'] = $server_port;
        }

        if ($server) {
            $conf['server'] = $server;
        }
        if ($local_port) {
            $conf['local_port'] = $local_port;
        }

        if ($localIP) {
            $conf['local_address'] = $localIP;
        }

        $local = DdLocaService::create('upadd_dd_local', 'tcp://0.0.0.0:6666');
        $local->getConfig($conf);
        $local->start();
    }


    /**
     * php console.php --u=dd --p=test
     */
    public function test()
    {
        $conf = Config::get('dd@serverConfig');
        $json = base64_encode(json($conf));
        echo $json;
    }


}