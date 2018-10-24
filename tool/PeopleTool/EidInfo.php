<?php

namespace tool\PeopleTool;

use tool\PeopleTool\VerifyEid;

class EidInfo
{

    /**
     * 判断身份证号码
     * @param $eid
     * @return bool
     */
    public static function is_eid($eid)
    {
        if (VerifyEid::is_eid($eid)) {
            return true;
        }
        return false;
    }

    /**
     * 获取星座
     * @param $eid
     * @return string
     */
    public static function getConstellation($eid = null)
    {
        self::is_eid($eid);

        $bir = substr($eid, 10, 4);
        $month = (int)substr($bir, 0, 2);
        $day = (int)substr($bir, 2);
        $strValue = '';
        if (($month == 1 && $day >= 20) || ($month == 2 && $day <= 18)) {
            $strValue = "水瓶座";
        } else if (($month == 2 && $day >= 19) || ($month == 3 && $day <= 20)) {
            $strValue = "双鱼座";
        } else if (($month == 3 && $day > 20) || ($month == 4 && $day <= 19)) {
            $strValue = "白羊座";
        } else if (($month == 4 && $day >= 20) || ($month == 5 && $day <= 20)) {
            $strValue = "金牛座";
        } else if (($month == 5 && $day >= 21) || ($month == 6 && $day <= 21)) {
            $strValue = "双子座";
        } else if (($month == 6 && $day > 21) || ($month == 7 && $day <= 22)) {
            $strValue = "巨蟹座";
        } else if (($month == 7 && $day > 22) || ($month == 8 && $day <= 22)) {
            $strValue = "狮子座";
        } else if (($month == 8 && $day >= 23) || ($month == 9 && $day <= 22)) {
            $strValue = "处女座";
        } else if (($month == 9 && $day >= 23) || ($month == 10 && $day <= 23)) {
            $strValue = "天秤座";
        } else if (($month == 10 && $day > 23) || ($month == 11 && $day <= 22)) {
            $strValue = "天蝎座";
        } else if (($month == 11 && $day > 22) || ($month == 12 && $day <= 21)) {
            $strValue = "射手座";
        } else if (($month == 12 && $day > 21) || ($month == 1 && $day <= 19)) {
            $strValue = "魔羯座";
        }
        return $strValue;
    }


    /**
     *  根据身份证号，自动返回对应的生肖
     * @param $eid
     * @return string
     */
    public static function getZodiac($eid)
    {
        self::is_eid($eid);
        $start = 1901;
        $end = $end = (int)substr($eid, 6, 4);
        $x = ($start - $end) % 12;
        $value = "";
        if ($x == 1 || $x == -11) {
            $value = "鼠";
        }
        if ($x == 0) {
            $value = "牛";
        }
        if ($x == 11 || $x == -1) {
            $value = "虎";
        }
        if ($x == 10 || $x == -2) {
            $value = "兔";
        }
        if ($x == 9 || $x == -3) {
            $value = "龙";
        }
        if ($x == 8 || $x == -4) {
            $value = "蛇";
        }
        if ($x == 7 || $x == -5) {
            $value = "马";
        }
        if ($x == 6 || $x == -6) {
            $value = "羊";
        }
        if ($x == 5 || $x == -7) {
            $value = "猴";
        }
        if ($x == 4 || $x == -8) {
            $value = "鸡";
        }
        if ($x == 3 || $x == -9) {
            $value = "狗";
        }
        if ($x == 2 || $x == -10) {
            $value = "猪";
        }
        return $value;
    }


    /**
     * 根据身份证号，自动返回性别
     * @param $eid
     * @param int $type in 1 return string or 0 return int 1->男 || 2->女
     * @return int|string
     */
    public static function getSex($eid, $type = 1)
    {
        self::is_eid($eid);
        $sexint = (int)substr($eid, 16, 1);
        $val = '';
        if ($type) {
            $val = $sexint % 2 === 0 ? '女' : '男';
        } else {
            $val = $sexint % 2 === 0 ? 2 : 1;
        }
        return $val;
    }

    /**
     * 获取周岁
     * @param $eid
     * @return float|string
     */
    public static function getAge($eid)
    {
        if (VerifyEid::is_eid($eid) === false) {
            return '';
        }
        $date = strtotime(substr($eid, 6, 8));
        //获得出生年月日的时间戳
        $today = strtotime('today');
        //获得今日的时间戳
        $diff = floor(($today - $date) / 86400 / 365);
        //得到两个日期相差的大体年数
        //strtotime加上这个年数后得到那日的时间戳后与今日的时间戳相比
        $age = strtotime(substr($eid, 6, 8) . ' +' . $diff . 'years') > $today ? ($diff + 1) : $diff;
        return $age;
    }


    /**
     * 获取生日
     * @param $eid
     * @return string
     */
    public static function getBirthday($eid)
    {
        if (VerifyEid::is_eid($eid) === false) {
            return '';
        }
        $year = null;
        $month = null;
        $day = null;
        $_birthday = null;
        if (strlen($eid) == 18) {
            $year = intval(substr($eid, 6, 4));
            $month = intval(substr($eid, 10, 2));
            $day = intval(substr($eid, 12, 2));
            $_birthday = $year . "-" . $month . "-" . $day;
        } elseif (strlen($eid) == 15) {
            $year = intval("19" . substr($eid, 6, 2));
            $month = intval(substr($eid, 8, 2));
            $day = intval(substr($eid, 10, 2));
            $_birthday = $year . "-" . $month . "-" . $day;
        } else {
            $_birthday = '';
        }
        return $_birthday;
    }


    /**
     * 返回所有
     * @param $eid
     * @return array
     */
    public static function getAll($eid)
    {
        return array(
            //周岁
            'age' => self::getAge($eid),
            //性别
            'sex' => self::getSex($eid),
            //生肖
            'zodiac' => self::getZodiac($eid),
            //星座
            'constellation' => self::getConstellation($eid),
            //生日
            'birthday' => self::getBirthday($eid),
        );
    }

}