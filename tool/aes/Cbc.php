<?php
namespace tool\aes;

class Cbc
{
    private $cipher = "AES-256-CBC";

    private $iv = '';

    /**
     * 加解密的key
     * @var string
     */
    public $key = '';

    /**
     * @var null
     */
    public $data = null;

    private $content = '';

    public function __construct()
    {
        $this->setIv();
    }

    /**
     * @return static
     */
    public static function __init()
    {
        return new static();
    }

    /**
     * 加密
     * @return string
     */
    public function encrypt()
    {
        $this->content = openssl_encrypt($this->data, $this->cipher, $this->key, $options = 0, $this->iv);
        if ($this->content) {
            return $this->content;
        }
        return false;
    }

    /**
     * 解密
     */
    public function decrypt()
    {
        $this->content = openssl_decrypt($this->data, $this->cipher, $this->key, $options = 0, $this->iv);
        if ($this->content) {
            return $this->content;
        }
        return false;
    }

    /**
     * @return string
     */
    private function setIv()
    {
        $ivlen = openssl_cipher_iv_length($this->cipher);
        $this->iv = openssl_random_pseudo_bytes($ivlen);
    }


    /**
     *
     */
    public function demo()
    {

        $this->data = [
            1, 2, 3, 4, 5, 6, 7, 8, 8, 9, [
                "info" => 11111,
                "zmq" => '你好呀哈哈哈哈',
            ],
        ];

        $this->data = json_encode($this->data);
        $this->key = '0f6a502b73ea206549ffb881b1028204d53f4349';
        var_dump($this->encrypt());
//        $this->data = $this->encrypt();
//        echo $this->decrypt();
    }
}