<?php
return [


    //是否为守护进程
    'daemonize' => true,

    'dd_server' => [
        'timeout' => 60,
        'reactor_num' => 6,
        'worker_num' => 10,
        'max_request' => 0,
        'backlog' => 128,
        'dispatch_mode' => 2,
        'log_file' => host() . '/data/dd_swoole.logs',
    ],


    'dd_local' => [
        'timeout' => 1,
        'poll_thread_num' => 1,
        'worker_num' => 2,
        'backlog' => 128,
        'dispatch_mode' => 2,
        'log_file' => host() . '/data/dd_local_swoole.logs',
    ],


    'local_config' => [
        'local_address' => '0.0.0.0',
        'local_port' => 1086,
        'server' => '127.0.0.1',
        'server_port' => 3131,
        'password' => '123456',
        'method' => 'aes-256-cfb'
    ],

    /**
     * server port in password
     */
    'serverConfig' => [
        3131 => 'password'
    ],

];
