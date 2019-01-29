<?php
return [

    //是否为守护进程
    'daemonize' => false,

    ########################server

    'serverConfig' => [
        'timeout' => 20,
        'worker_num' => 4,
        'max_request' => 10000,
        'log_file' => host() . '/data/console/mq_server.logs',
        'task_tmpdir' => host() . '/data/console/task',
        'debug_mode' => 1,
        'task_worker_num' => 6,
        'dispatch_mode' => 2,

        //收发问题
//        'open_eof_check' => true,
//        'open_eof_split' => true,
//        'package_eof' => '\r\n\r\n',


        //关闭Nagle合并算法
        'open_tcp_nodelay' => true,
        'package_length_type' => 'N',
        'package_length_offset' => 0,
        'package_body_offset' => 0,

        //最大包长度
        'package_max_length' => 2097152,
        'buffer_output_size' => 3145728, //1024 * 1024 * 3,
        'pipe_buffer_size' => 33554432, // 1024 * 1024 * 32,
        'backlog' => 128,
    ],




];
