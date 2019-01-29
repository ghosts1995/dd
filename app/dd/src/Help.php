<?php

namespace app\dd\src;


use app\dd\src\DdConfig;
use Log;
use app\model\ProxyPoolModel;


class Help
{

    /**
     * @param $data
     * @param $key
     * @return bool|string
     */
    public static function hashAuthGen($data, $key)
    {
        $Sig = hash_hmac('sha1', $data, $key, true);
        return substr($Sig, 0, DdConfig::ONETIMEAUTH_BYTES);
    }


    /*
   * UDP 部分 返回客户端 头部数据 by @Zac
   * 生成UDP header 它这里给返回解析出来的域名貌似给udp dns域名解析用的
   */
    public static function packHeader($addr, $addr_type, $port)
    {
        $header = '';
        //$ip = pack('N',ip2long($addr));
        //判断是否是合法的公共IPv4地址，192.168.1.1这类的私有IP地址将会排除在外
        /*
        if(filter_var($addr, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE)) {
            // it's valid
            $addr_type = ADDRTYPE_IPV4;
        //判断是否是合法的IPv6地址
        }elseif(filter_var($addr, FILTER_VALIDATE_IP, FILTER_FLAG_NO_RES_RANGE)){
            $addr_type = ADDRTYPE_IPV6;
        }
        */
        switch ($addr_type) {
            case DdConfig::ADDRTYPE_IPV4:
                $header = b"\x01" . inet_pton($addr);
                break;
            case DdConfig::ADDRTYPE_IPV6:
                $header = b"\x04" . inet_pton($addr);
                break;
            case DdConfig::ADDRTYPE_HOST:
                if (strlen($addr) > 255) {
                    $addr = substr($addr, 0, 255);
                }
                $header = b"\x03" . chr(strlen($addr)) . $addr;
                break;
            default:
                return;
        }
        return $header . pack('n', $port);
    }



    /**
     * 解析shadowsocks客户端发来的socket5头部数据
     * @param $otaEnable
     * @param $buffer
     * @return array|bool
     */
    public static function socket5Header($buffer, $otaEnable = false)
    {
        $addr_type = ord($buffer[0]);
        $dest_addr = 0;
        $port_data = 0;
        $dest_port = 0;
        $header_length = 0;
        $ota_enable = false;
        if ($otaEnable || ($addr_type & DdConfig::ADDRTYPE_AUTH) == DdConfig::ADDRTYPE_AUTH) {
            $ota_enable = true;
            //把第四位值空0b00010000==>0b00000000
            $addr_type = $addr_type ^ DdConfig::ADDRTYPE_AUTH;
        }

        switch ($addr_type) {

            case DdConfig::ADDRTYPE_IPV4:
                    Log::cmd("ADDRTYPE_IPV4");
                    $dest_addr = ord($buffer[1]) . '.' . ord($buffer[2]) . '.' . ord($buffer[3]) . '.' . ord($buffer[4]);
                    $port_data = unpack('n', substr($buffer, 5, 2));
                    $dest_port = $port_data[1];
                    $header_length = 7;
                break;

            case DdConfig::ADDRTYPE_HOST:
                    Log::cmd("ADDRTYPE_HOST");
                    $addrlen = ord($buffer[1]);
                    $dest_addr = substr($buffer, 2, $addrlen);
                    $port_data = unpack('n', substr($buffer, 2 + $addrlen, 2));
                    $dest_port = $port_data[1];
                    $header_length = $addrlen + 4;
                break;

            case DdConfig::ADDRTYPE_IPV6:
                    Log::cmd("@@@@@@@@@@IPV6 todo ipv6 not support yet@@@@@@@@@@@@@@@@@@");
                break;

            default:
                    Log::cmd("unsupported addr type $addr_type");
                break;
        }
        //将是否是 OTA 一并返回
        $header = [$addr_type, $dest_addr, $dest_port, $header_length, $ota_enable];
//        $json = json($header);
//        Log::cmd("header:{$json}");
        return $header;
    }


    /**
     * @return bool
     */
    public static function getProxy()
    {
        $proxyList = ProxyPoolModel::getProxyList();
        if ($proxyList) {
            $index = rand(0, count($proxyList));
            if (!isset($proxyList[$index])) {
                return false;
            }
            $data = $proxyList[$index];
            $proxy['socks5_host'] = $data['ip'];
            $proxy['socks5_port'] = $data['port'];
            if ((int)$data['is_user'] == 2) {
                $proxy['socks5_username'] = $data['user'];
                $proxy['socks5_password'] = $data['passwd'];
            }
            return $proxy;
        } else {
            return false;
        }
    }

}