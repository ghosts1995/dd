<?php

namespace controller\query;


use Data;
use Log;
use tool\Help;

use app\netword\GeoIP;

class IpAction extends \Upadd\Frame\Action
{

    /**
     * @note  该版本为IP地区,后续添加IDC IP库
     * @return array
     */
    public function outputIP()
    {
        $type = Data::get('type', 'ip');
        $d = Data::get('d');
        $http_server = Data::get('HttpServer');
        $clinetIP = null;
        if (isset($http_server->server['remote_addr'])) {
            $clinetIP = $http_server->server['remote_addr'];
        } else {
            $clinetIP = getClient_id();
        }
        $ok = [];
        if ($clinetIP) {
            if ($type == 'json') {
                $ip = '';
                if ($clinetIP && !$d) {
                    $ip = $clinetIP;
                } else {
                    $ip = $d;
                }

                if (help::is_ip($ip)) {
                    $geoIPData = GeoIP::get($ip);
                    if ($geoIPData) $ok = array_merge($geoIPData);
                }
                $ok['ip'] = $ip;

                return ok($ok);
            } elseif ($type == 'ip') {
                return $clinetIP;
            }


        } else {
            return error('gain failure');
        }
    }


}