<?php

namespace tool;

class RsaCa
{

    /**
     * 私钥
     * @var string
     */
    protected $private_key = '';


    /**
     * 公钥
     * @var string
     */
    protected $public_key = '';


    public function __construct()
    {

    }


    /**
     * 生产RSA证书 默认私钥1024
     * @return array
     */
    public static function add()
    {
        //创建公钥和私钥
        $res = openssl_pkey_new(array('private_key_bits' => 1024));
        //提取私钥
        openssl_pkey_export($res, $private_key);
        //生成公钥
        $public_key = openssl_pkey_get_details($res);
        return ['private_key' => $private_key, 'public_key' => $public_key['key']];
    }


    /**
     * 生成公钥和私钥并设置路径
     * 默认后缀.pem
     * @param null $private_paht 私钥路径
     * @param null $public_paht 公钥路径
     */
    public static function setAddPath($private_paht = null, $public_paht = null)
    {
        $key = self::add();
        //生成公钥文件
        $fp = fopen($public_paht, "w");
        fwrite($fp, $key['public_key']);
        fclose($fp);
        //生成密钥文件
        $fp = fopen($private_paht, "w");
        fwrite($fp, $key['private_key']);
        fclose($fp);
        return true;
    }


    /**
     * 签名
     * @param string $data
     * @return string
     */
    public function sign($data, $private_key)
    {
        //私钥转换为openssl密钥，必须是没有经过pkcs8转换的私钥
        $res = openssl_get_privatekey($private_key);
        //调用openssl内置签名方法，生成签名$sign
        openssl_sign($data, $sign, $res);
        //释放资源
        openssl_free_key($res);
        //base64编码
        $sign = base64_encode($sign);
        return $sign;
    }

    /**
     * 待签名数据
     * $sign需要验签的签名
     * 验签公钥
     * return 验签是否通过 bool值
     */
    public function verify($data, $sign, $public_key)
    {
        //公钥转换为openssl格式密钥
        $res = openssl_get_publickey($public_key);
        //调用openssl内置方法验签，返回bool值
        $result = (bool)openssl_verify($data, base64_decode($sign), $res);
        //释放资源
        openssl_free_key($res);
        //返回资源是否成功
        return $result;
    }


    /**
     * 私钥加密
     * @param string $data
     * @return string
     */
    public function privateEncrypt($data, $private_key)
    {
        //私钥加密后的数据
        openssl_private_encrypt($data, $encrypted, $private_key);
        //加密后的内容通常含有特殊字符，需要base64编码转换下
        $encrypted = base64_encode($encrypted);
        return $encrypted;
    }


    /**
     * 公钥解密
     * @param string $encrypted
     * @return bool
     */
    public function publicDecrypt($encrypted, $public_key)
    {
        openssl_public_decrypt(base64_decode($encrypted), $decrypted, $public_key);
        return $decrypted;
    }


    /**
     * 公钥加密
     * @param string $data
     * @return string
     */
    public function publicEncrypt($data, $public_key)
    {
        openssl_public_encrypt($data, $encrypted, $public_key);
        return base64_encode($encrypted);
    }


    /**
     * 私钥解密
     * @param string $encrypted
     * @return bool
     */
    public function private_decrypt($encrypted, $private_key)
    {
        openssl_private_decrypt(base64_decode($encrypted), $decrypted, $private_key);
        return $decrypted;
    }

}