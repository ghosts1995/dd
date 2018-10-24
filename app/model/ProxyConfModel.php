<?php

namespace cp\model;

use Model;

class ProxyConfModel extends Model
{

    protected $_table = 'proxy_conf';

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


}