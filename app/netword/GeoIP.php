<?php

namespace app\netword;

use GeoIp2\Database\Reader;

class GeoIP
{

    /**
     * @param string $ip
     * @return array
     * @throws \GeoIp2\Exception\AddressNotFoundException
     * @throws \MaxMind\Db\Reader\InvalidDatabaseException
     */
    private static function setIP($ip)
    {
        $hostDir = host();
        $reader = new Reader($hostDir . '/tool/data/GeoList2/City/GeoLite2-City.mmdb');
        try {
            $record = $reader->city($ip);
            $data = [];
            $data['country'] = [
                'code' => $record->country->isoCode, // 'US'
                'name' => $record->country->name, // 'United States'
                'names' => $record->country->names['zh-CN'], // '美国'
            ];

            $data['province'] = [
                // 'Minnesota'
                'name' => isset($record->mostSpecificSubdivision->name) ? $record->mostSpecificSubdivision->name : '',
                // 'MN'
                'code' => isset($record->mostSpecificSubdivision->isoCode) ? $record->mostSpecificSubdivision->isoCode : '',
            ];
            // 'Minneapolis'
            $data['city'] = $record->city->name;
            $data['postal'] = $record->postal->code;
            $data['latitude'] = $record->location->latitude;
            $data['longitude'] = $record->location->longitude;
            return $data;
        } catch (\Exception $exception) {
//            print_r($exception);
            return false;
        }
    }


    /**
     * @param $ip
     * @return array
     */
    public static function get($ip)
    {
        return self::setIP($ip);
    }


}