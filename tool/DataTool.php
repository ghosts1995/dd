<?php

namespace tool;

class DataTool
{


    public static function init()
    {
        return new static();
    }


    /**
     * 求两个日期之间相差的天数
     * (针对1970年1月1日之后，求之前可以采用泰勒公式)
     * @param string $day1
     * @param string $day2
     * @return number
     */
    public function diffBetweenTwoDays($start, $end)
    {
        $startDate = strtotime($start);
        $endDate = strtotime($end);
        if ($startDate < $endDate) {
            $tmp = $endDate;
            $endDate = $startDate;
            $startDate = $tmp;
        }
        return ($startDate - $endDate) / 86400;
    }

    /**
     * 求两个时间之间相差的时间
     * @param $start
     * @param $end
     * @return float|int
     *
     */
    public function diffBetweenTwoHour($start, $end)
    {
        $startDate = strtotime($start);
        $endDate = strtotime($end);
        if ($startDate < $endDate) {
            if ($startDate < $endDate) {
                $tmp = $endDate;
                $endDate = $startDate;
                $startDate = $tmp;
            }
        }
        return floor($startDate - $endDate)%86400/3600;
    }


    //当前时间戳
    public function now()
    {
        return date('Y-m-d H:i:s', strtotime('now'));
    }


    //当前时间戳+1秒 2017-01-09 21:04:12
    public function second()
    {
        return date('Y-m-d H:i:s', strtotime('1second'));
    }

    //当前时间戳+1分
    public function minute()
    {
        date('Y-m-d H:i:s', strtotime('+1minute'));
    }

    ////当前时间戳+1小时
    public function hour()
    {
        date('Y-m-d H:i:s', strtotime('+1hour'));
    }

    /**
     * 当前时间戳+天
     * @param int $num
     * @param null $date
     * @param string $second
     * @return false|string
     */
    public function day($num = 1, $date = null, $second = 'H:i:s')
    {
        $day = '+'.$num.'day';
        if ($date == null)
        {
            return date('Y-m-d', strtotime($day));
        } else {
            return date('Y-m-d', strtotime($day, $date));
        }
    }

    /**
     * 当前时间戳+1周
     * @param int $num
     */
    public function week($num = 1, $date = null)
    {
        if ($date == null) {
            return date('Y-m-d', strtotime("+{$num}week"));
        } else {
            return date('Y-m-d', strtotime("+{$num}week", $date));
        }
    }


    /**
     * 默认当前时间,获取未来一个月时间
     * @param int $num
     * @param $date 指定时间的时间戳
     * @return false|string
     */
    public function month($num = 1,$date=null)
    {
        if ($date == null) {
            return date('Y-m-d', strtotime("+{$num}month"));
        } else {
            return date('Y-m-d', strtotime("+{$num}month", $date));
        }
    }


    /**
     * 当前时间戳+年
     * @return 默认加一年
     * @param int $num
     */
    public function year($num = 1)
    {
        return date('Y-m-d H:i:s', strtotime("+{$num}year"));
    }


}