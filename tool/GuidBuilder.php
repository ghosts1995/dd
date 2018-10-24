<?php

namespace tool;


class GuidBuilder
{

    /**
     * 创建Guid
     * @return string
     */
    public static function create()
    {
        $guid = self::add();
        return strtoupper($guid);
    }

    final private static function add()
    {
        $microTime = microtime();
        list($dec, $sec) = explode(" ", $microTime);
        $dec_hex = dechex($dec * 1000000);
        $sec_hex = dechex($sec);
        self::ensureLength($dec_hex, 5);
        self::ensureLength($sec_hex, 6);
        $guid = '';
        $guid .= $dec_hex;
        $guid .= self::createGuidSection(3);
        $guid .= '-';
        $guid .= self::createGuidSection(4);
        $guid .= '-';
        $guid .= self::createGuidSection(4);
        $guid .= '-';
        $guid .= self::createGuidSection(4);
        $guid .= '-';
        $guid .= $sec_hex;
        $guid .= self::createGuidSection(6);
        return $guid;
    }


    final private static function ensureLength($string = '', $length = null)
    {
        $strlen = strlen($string);
        if ($strlen < $length) {
            $string = str_pad($string, $length, "0");
        } else if ($strlen > $length) {
            $string = substr($string, 0, $length);
        }
        return $string;
    }


    /**
     * 创建Guid长度
     * @param null $characters
     * @return string
     */
    final private static function createGuidSection($characters = null)
    {
        $return = '';
        for ($i = 0; $i < $characters; $i++) {
            $return .= dechex(mt_rand(0, 15));
        }
        return $return;
    }


}