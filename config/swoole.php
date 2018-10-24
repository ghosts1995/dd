<?php

return [

    /**
     * true 进程服务
     * false = 会话模式
     */
    'daemonize' => false,

    'tcp' => [
        'name' => 'upadd_tcp',
        'host' => 'tcp://127.0.0.1:9981',
    ],

    'http' => [
        'name' => 'upadd_http',
        'host' => 'http://0.0.0.0:666',
    ],

    'webSocket' => [
        'name' => 'upadd_webSocket',
        'host' => 'ws://127.0.0.1:8081',
    ],

    'udp' => [
        'name' => 'upadd_upd',
        'host' => 'upd://127.0.0.1:9978',
    ],


    /**
     * TCP服务参数
     */
    'tcp_param' => [
        'worker_num' => 4,
        'max_request' => 10000,
        'log_file' => host() . '/data/console/swoole.logs',
        'debug_mode' => 1,
        'daemonize' => false,
//        'task_worker_num' =>4,
        'dispatch_mode' => 3,
        //收发问题
        'open_eof_check' => true,
        'open_eof_split' => true,
        //关闭Nagle合并算法
        'open_tcp_nodelay' => true,
        'package_length_type' => 'N',
        'package_length_offset' => 0,
        'package_body_offset' => 0,

        //最大包长度
        'package_max_length' => 2097152,
        'buffer_output_size' => 3145728, //1024 * 1024 * 3,
        'pipe_buffer_size' => 33554432, // 1024 * 1024 * 32,
        'package_eof' => '\r\n\r\n',
        'backlog' => 3000,
    ],


    /**
     * TCP任务服务参数
     */
    'tcp_task_param' => [
        'worker_num' => 4,
        'max_request' => 10000,
        'log_file' => host() . '/data/console/tcp_task_param.logs',
        'task_tmpdir' => host() . '/data/console/task',
        'debug_mode' => 1,
        'daemonize' => false,
        'task_worker_num' => 4,
        'dispatch_mode' => 3,

        //收发问题
        'open_eof_check' => true,
        'open_eof_split' => true,
        //关闭Nagle合并算法
        'open_tcp_nodelay' => true,
        'package_length_type' => 'N',
        'package_length_offset' => 0,
        'package_body_offset' => 0,

        //最大包长度
        'package_max_length' => 2097152,
        'buffer_output_size' => 3145728, //1024 * 1024 * 3,
        'pipe_buffer_size' => 33554432, // 1024 * 1024 * 32,
        'package_eof' => '\r\n\r\n',
        'backlog' => 3000,
    ],


    /**
     * http服务参数
     */
    'httpParam' => [
        'worker_num' => 6,
        'daemonize' => 0,
        'max_request' => 10000,
        'dispatch_mode' => 2,
        'debug_mode' => 1,
        'log_file' => host() . '/data/swoole_http.logs',
    ],

    /**
     * web socket
     */
    'webSocketParam' => [],

    'udpParam' => [],


    ////clinet
    'tcpClinet' => [
        'socket_buffer_size' => 1024 * 1024 * 2, //2M缓存区
        'open_length_check' => 1,       // 开启协议解析
        'package_length_type' => 'N',     // 长度字段的类型
        'package_length_offset' => 0,       //第N个字节是包长度的值
        'package_body_offset' => 4,       //第N个字节开始计算长度
        'package_max_length' => 2000000,  //协议最大长度
        ////////EOF检测
        'open_eof_split' => true,   // 开启EOF检测
        'package_eof' => '\r\n\r\n',   // 设置EOF标记
    ],




];