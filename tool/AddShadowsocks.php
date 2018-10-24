<?php

namespace ops\user;

class AddShadowsocks
{

    public $server = '0.0.0.0';

    public $local_address = '127.0.0.1';

    public $local_port = '1080';

    //用户池
    public $port_password = [];

    //超时时间
    public $timeout = 300;

    public $method = 'aes-256-cfb';

    public $fast_open = 'false';

    //参数
    public $param = [];

    //设置默认用户数
    public $number = 15;

    /**
     * @param int $length
     * @return int
     */
    private function randomPort($length = 4)
    {
        return rand(pow(10, ($length - 1)), pow(10, $length) - 1);
    }

    /**
     * @param $length
     * @return string
     */
    private function randomPasswd($length)
    {
        $returnStr = '';
        $pattern = '1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLOMNOPQRSTUVWXYZ';
        for ($i = 0; $i < $length; $i++) {
            $returnStr .= $pattern{mt_rand(0, 61)}; //生成php随机数
        }
        return $returnStr;
    }


    private function synthesis()
    {
        $this->param = [
            'server' => $this->server,
            'local_address' => $this->local_address,
            'local_port' => $this->local_port,
            'port_password' => $this->port_password,
            'timeout' => $this->timeout,
            'method' => $this->method,
            'fast_open' => $this->fast_open,
        ];
    }

    public function generate()
    {
        $data = [];
        for ($i = 0; $i < $this->number; $i++) {
            $port = $this->randomPort();
            $passwd = $this->randomPasswd(8);
            $data[$port] = $passwd;
        }
        $this->port_password = $data;
        $this->synthesis();
        $json = json($this->param);
        if (file_put_contents('shadowsocks.json', $json)) {
            echo "ok\n";
        } else {
            echo "no\n";
        }
        $this->emailInfo();
    }

    private function emailInfo()
    {
        $body = '地址:' . $this->server . "\n\r";
        $body .= '加密规则:' . $this->method . "\n\r";
        $body .= '端口和密码' . "\n\r";
        foreach ($this->port_password as $k => $v) {
            $body .= "\r\r\r\r\r" . $k . "======" . $v . "\n\r";
        }
        echo $body;
    }

}