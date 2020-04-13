<?php

namespace Mostertb\TransmissionTools\Helper\TranmissionApi;


use Mostertb\TransmissionTools\Helper\Config\Config;
use Transmission\Client;
use Transmission\Transmission;

class TransmissionApiClientFactory
{

    /**
     * @param string      $host
     * @param integer     $port
     * @param string|null $username
     * @param string|null $password
     * @param int|null    $timeout
     *
     * @return Transmission
     */
    public static function makeApiClient($host, $port, $username, $password, $timeout = null)
    {
        if(is_null($timeout)){
            $timeout = Config::getInstance()->getHttpTimeout();
        }

        $httpClient = new Client($host, $port, null, $timeout);
        if(!is_null($username)){
            $httpClient->authenticate($username, $password);
        }

        $apiClient = new Transmission();
        $apiClient->setClient($httpClient);

        return $apiClient;
    }
}