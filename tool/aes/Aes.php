<?php
namespace tool;

class Aes
{

    protected $_cipher = "rijndael-128";
    protected $_mode = "cbc";
    protected $_key;
    protected $_iv = null;
    protected $_descriptor = null;

    /**
     * 是否按照PKCS #7 的标准进行填充
     * 为否默认将填充“\0”补位
     * @var boolean
     */
    protected $_PKCS7 = false;

    /**
     * 构造函数，对于密钥key应区分2进制字符串和16进制的。
     * 如需兼容PKCS#7标准，应选项设置开启PKCS7，默认关闭
     * @param string $key
     * @param mixed $iv 向量值
     * @param array $options
     */
    public function __construct($key = null, $iv = null, $options = null)
    {
        if (null !== $key) {
            $this->setKey($key);
        }

        if (null !== $iv) {
            $this->setIv($iv);
        }

        if (null !== $options) {
            if (isset($options['chipher'])) {
                $this->setCipher($options['chipher']);
            }

            if (isset($options['PKCS7'])) {
                $this->isPKCS7Padding($options['PKCS7']);
            }

            if (isset($options['mode'])) {
                $this->setMode($options['mode']);
            }
        }
    }

    /**
     * PKCS#7状态查看，传入Boolean值进行设置
     * @param  boolean $flag
     * @return boolean
     */
    public function isPKCS7Padding($flag = null)
    {
        if (null === $flag) {
            return $this->_PKCS7;
        }
        $this->_PKCS7 = (bool)$flag;
    }

    /**
     * 开启加密算法
     * @param  string $algorithm_directory locate the encryption
     * @param  string $mode_directory
     * @return Crypt_AES
     */
    public function _openMode($algorithm_directory = "", $mode_directory = "")
    {
        $this->_descriptor = mcrypt_module_open($this->_cipher, $algorithm_directory, $this->_mode, $mode_directory);
        return $this;
    }

    public function getDescriptor()
    {
        if (null === $this->_descriptor) {
            $this->_openMode();
        }
        return $this->_descriptor;
    }

    protected function _genericInit()
    {
        return mcrypt_generic_init($this->getDescriptor(), $this->getKey(), $this->getIv());
    }

    protected function _genericDeinit()
    {
        return mcrypt_generic_deinit($this->getDescriptor());
    }

    public function getMode()
    {
        return $this->_mode;
    }

    public function setMode($mode)
    {
        $this->_mode = $mode;
        return $this;
    }

    public function getCipher()
    {
        return $this->_cipher;
    }

    public function setCipher($cipher)
    {
        $this->_cipher = $cipher;
        return $this;
    }

    /**
     * 获得key
     * @return string
     */
    public function getKey()
    {
        return $this->_key;
    }

    /**
     * 设置可以
     * @param string $key
     */
    public function setKey($key)
    {
        $this->_key = $key;
        return $this;
    }


    /**
     * 获得加密向量块,如果其为null时将追加当前Descriptor的IV大小长度
     *
     * @return string
     */
    public function getIv()
    {
        if (null === $this->_iv && in_array($this->_mode, array("cbc", "cfb", "ofb",))) {
            $size = mcrypt_enc_get_iv_size($this->getDescriptor());
            $this->_iv = str_pad("", 16, "\0");
        }
        return $this->_iv;
    }

    /**
     * 获得向量块
     *
     * @param  string $iv
     * @return Crypt_AES $this
     */
    public function setIv($iv)
    {
        $this->_iv = $iv;
        return $this;
    }

    /**
     * 加密
     * @param  string $str 被加密文本
     * @return string
     */
    public function encrypt($str)
    {
        $td = $this->getDescriptor();
        $this->_genericInit();
        $bin = mcrypt_generic($td, $this->padding($str));
        $this->_genericDeinit();
        return $bin;
    }

    public function padding($dat)
    {
        if ($this->isPKCS7Padding()) {
            $block = mcrypt_enc_get_block_size($this->getDescriptor());

            $len = strlen($dat);
            $padding = $block - ($len % $block);
            $dat .= str_repeat(chr($padding), $padding);
        }

        return $dat;
    }

    public function unpadding($str)
    {
        if ($this->isPKCS7Padding()) {
            $pad = ord($str[($len = strlen($str)) - 1]);
            $str = substr($str, 0, strlen($str) - $pad);
        }
        return $str;
    }

    /**
     * 解密
     * @param  string $str
     * @return string
     */
    public function decrypt($str = null)
    {
        if ($str) {
            $td = $this->getDescriptor();
            $this->_genericInit();
            $text = mdecrypt_generic($td, $str);
            $this->_genericDeinit();

            return $this->unpadding($text);
        }
        return false;
    }

    /**
     * 16进制转成2进制数据
     * @param  string $hexdata 16进制字符串
     * @return string
     */
    public static function hex2bin($hexdata)
    {
        return pack("H*", $hexdata);
    }

    /**
     * 字符串转十六进制
     * @param  string $hexdata 16进制字符串
     * @return string
     */
    public static function strToHex($string)
    {
        $hex = '';
        for ($i = 0; $i < strlen($string); $i++) {
            $hex .= dechex(ord($string[$i]));
        }
        $hex = strtoupper($hex);
        return $hex;
    }

    /**
     * 十六进制转字符串
     * @param  string $hexdata 16进制字符串
     * @return string
     */
    public function hexToStr($hex)
    {
        $string = '';
        for ($i = 0; $i < strlen($hex) - 1; $i += 2) {
            $string .= chr(hexdec($hex[$i] . $hex[$i + 1]));
        }
        return $string;
    }

}