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


    // php console.php --u=test --p=conf
    public function conf()
    {
        Log::cmd(Config::get('tag@test'));
    }

    /**
     * php console.php --u=test --p=proxy
     */
    public function proxy()
    {
        $new = new ProxyProcess();
        $new->test();
    }


}