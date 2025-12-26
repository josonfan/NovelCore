<?php

namespace utils;
use GeoIp2\Database\Reader;
class GeoIP2
{
    private $cityDbReader;
    public function __construct(){
        $this->cityDbReader= new Reader(app()->getRootPath().'extend/utils/GeoIP/GeoLite2-City.mmdb');
    }

    /**
     * @param $ip
     * @return \GeoIp2\Model\City
     * @throws \GeoIp2\Exception\AddressNotFoundException
     */
    public function getCity($ip): \GeoIp2\Model\City
    {
        return $this->cityDbReader->city($ip);
    }
}