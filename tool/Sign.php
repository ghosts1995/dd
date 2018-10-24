<?php
namespace tool;

use Upadd\Bin\UpaddException;

class Sign
{


    /**
     * 参数
     * @var array
     */
    public $params = [];

    /**
     * 秘钥
     * @var string
     */
    public $secret = '';


    /**
     * 验证参数
     * @throws UpaddException
     */
    private function verifyParams()
    {
        if (is_array($this->params) == false || empty($this->params)) {
            throw new UpaddException('Sign to params not array');
        }

        if (is_string($this->secret) == false || empty($this->secret)) {
            throw new UpaddException('Sign to secret not secret');
        }
    }


    /**
     * 获取签名
     * @return string
     */
    public function getSignature()
    {
        $str = '';
        ksort($this->params);
        foreach ($this->params as $k => $v)
        {
            $str .= "$k=$v";
        }
        $str .= $this->secret;
        return md5($str);
    }

    /**
     * 验证
     * @param string $sign
     * @return bool
     */
    public function verify($sign = '')
    {
        $this->verifyParams();
        $signature = $this->getSignature();
        if ($signature == $sign) {
            return true;
        }
        return false;
    }


}