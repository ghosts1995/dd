<?php

namespace tool\mq;


use Upadd\Bin\UpaddException;

class Unpack
{

    /**
     * @param $data
     * @return string
     */
    public static function encode($data)
    {
        $data = json($data);

        return (base64_encode($data));
//        return (base64_encode($data) . '\r\n\r\n');
    }


    public function isStrlenMax($data)
    {
        $data = (strlen($data) * 8);
        if ($data > 2048) {
            throw new UpaddException("Beyond the default maximum");
        }
    }


    /**
     * @param $data
     * @return \json
     */
    public static function decode($data)
    {
//        $data = explode('\r\n\r\n', $data);
//        if (isset($data[0])) {
//            $data = base64_decode($data[0]);
//            return json($data);
//        } else {
//            return false;
//        }


        $data = base64_decode($data);
        return json($data);
    }


}