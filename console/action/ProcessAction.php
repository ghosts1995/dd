<?php

namespace console\action;

use  Swoole\Process as swoole_process;

use Data;
use Config;
use Log;

use app\process\ProxyProcess;


class ProcessAction extends \Upadd\Frame\Action
{

    /**
     * php console.php --u=process --p=dow
     * php console.php --u=process --p=dow --d=1
     */
    public function dow()
    {
        $daemon = Data::get('d',0);
        if($daemon){
            swoole_process::daemon();
        }
        ProxyProcess::create()->runDowProxy();
        // 注册信号，回收退出的子进程
        swoole_process::signal(SIGCHLD, function ($sig)
        {
            while ($ret = swoole_process::wait(false))
            {
                Log::cmd("exit pid={$ret['pid']}");
            }
        });
    }


    /**
     * php console.php --u=process --p=proxy
     * php console.php --u=process --p=proxy --d=1
     */
    public function proxy()
    {
        $daemon = Data::get('d',0);
        if($daemon){
            swoole_process::daemon();
        }
        ProxyProcess::create()->runSetProxy();
        // 注册信号，回收退出的子进程
        swoole_process::signal(SIGCHLD, function ($sig)
        {
            while ($ret = swoole_process::wait(false))
            {
                Log::cmd("exit pid={$ret['pid']}");
            }
        });
    }


    /**
     * php console.php --u=process --p=test
     */
    public function test()
    {
//        swoole_process::daemon();
        $pro = ProxyProcess::create();
        $pro->test();
        exit;
        // 注册信号，回收退出的子进程
        swoole_process::signal(SIGCHLD, function ($sig)
        {
            while ($ret = swoole_process::wait(false))
            {
                Log::cmd("exit pid={$ret['pid']}");
            }
        });
    }


}