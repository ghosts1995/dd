<?php
return [


    //是否为守护进程
    'daemonize' => true,

    ########################server

    'dd_server' => [
        'timeout' => 20,
//        'reactor_num' => 6,
        'worker_num' => 10,
        'max_request' => 0,
        'backlog' => 128,
        'dispatch_mode' => 2,
        'log_file' => host() . '/data/dd_swoole.logs',
    ],


    /**
     * server port in password
     */
    'serverConfig' => [
        3131 => 'password'
    ],

    ########################localhost

    'dd_local' => [
        'timeout' => 15,
        'poll_thread_num' => 1,
        'worker_num' => 4,
        'backlog' => 128,
        'dispatch_mode' => 2,
        'log_file' => host() . '/data/dd_local_swoole.logs',
    ],


    'local_config' => [
        'local_address' => '0.0.0.0',
        'local_port' => 6666,
        'server' => '127.0.0.1',
        'server_port' => 3131,
        'password' => '123456',
        'method' => 'aes-256-cfb'
    ],



];
