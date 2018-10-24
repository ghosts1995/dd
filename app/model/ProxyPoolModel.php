<?php

namespace app\model;

use Model;

class ProxyPoolModel extends Model
{

    protected $_table = 'proxy_pool';

    protected $_primaryKey = 'id';


    public static function bySoArrayOne($SoArray = [])
    {
        $so['is_delete'] = 1;
        if ($SoArray) {
            $so = array_merge($SoArray, $so);
        }
        return self::where($so)->find();
    }


    public static function bySoArrayList($so)
    {
        return self::where($so)->sort('id', false)->get();
    }

    public static function getProxyList()
    {
        return self::bySoArrayList(['is_delete' => 1]);
    }

    public static function byIpMd5One($md5)
    {
        if ($md5) {
            $so['ip_md5'] = $md5;
        }
        return self::bySoArrayOne($so);
    }

    public static function getOne()
    {
        return self::where(['is_delete' => 1])->sort('update_time', true)->find();
    }

}