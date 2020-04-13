<?php

namespace Mostertb\TransmissionTools\Helper;


class Humanizer
{

    public static function dataRate($bytesPerSecond)
    {
        $units = [
            'B/s',
            'KB/s',
            'MB/s',
            'GB/s'
        ];

        $index = 0;
        while (($bytesPerSecond / 1024) >= 1 && $index < (count($units)-1)) {
            $bytesPerSecond = $bytesPerSecond / 1024;
            $index++;
        }

        $precision = 3;
        if($index == 0){
            $precision = 0; // don't show decimals for B/s
        }

        return number_format($bytesPerSecond, $precision, '.', '').' '.$units[$index];
    }
}