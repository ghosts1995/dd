<?php

namespace tool;

class Help
{


    /**
     * @param string $param
     * @return array|mixed
     */
    public static function baseJsonToArray(string $param): array
    {
        $array = [];
        $json = base64_decode($param);
        if ($json) {
            $array = json_decode($json, true);
        }
        return $array;
    }


    /**
     * get file type
     * @param $filename
     * @return bool|string
     */
    public static function getFileType($filename)
    {
        return substr($filename, strrpos($filename, '.') + 1);
    }

    /**
     * check ip
     * @param $ip
     * @return bool
     */
    public static function is_ip($ip)
    {
        //判断是否是合法IP
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * 验证邮箱
     * @param $email
     * @return bool
     */
    public static function is_email($email)
    {
        $reg = '/\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*/';
        if (preg_match($reg, $email)) {
            return true;
        }
        return false;
    }

    /**
     * 验证密码长度
     * @param $passwd
     * @return bool
     */
    public static function is_passwd_strlen($passwd, $long = 6, $breadth = 16)
    {
        if (strlen($passwd) >= $long && strlen($passwd) <= $breadth) {
            return true;
        }
        return false;
    }

    /**
     * 判断长度是否一致
     * @param $code
     * @param int $long
     * @param int $breadth
     * @return bool
     */
    public static function is_strlen($code, $long = 6, $breadth = 16)
    {
        if (strlen($code) == $long && strlen($code) == $breadth) {
            return true;
        }
        return false;
    }


    /**
     * the is china phone number
     * @param null $mobile
     * @return bool
     */
    public static function isChinaPhone(int $mobile)
    {
        if (!is_numeric($mobile)) {
            return false;
        }
        return (preg_match('#^13[\d]{9}$|^14[5,7]{1}\d{8}$|^15[^4]{1}\d{8}$|^17[0,6,7,8]{1}\d{8}$|^18[\d]{9}$#', $mobile) ? true : false);
    }


}