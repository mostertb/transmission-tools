<?php

use Mostertb\TransmissionTools\Helper\Humanizer;
use PHPUnit\Framework\TestCase;

class HumanizerTest extends TestCase
{

    /**
     * @dataProvider dataRateProvider
     * 
     * @param $bytesPerSecond
     * @param $expected
     */
    public function testDataRate($bytesPerSecond, $expected)
    {
        $this->assertEquals($expected, Humanizer::dataRate($bytesPerSecond));
    }

    public function dataRateProvider()
    {
        return [
            [32, '32 B/s'],
            [982345, '959.321 KB/s'],
            [823456789, '785.310 MB/s'],
            [823456789123, '766.904 GB/s'],
            [1193456789123, '1111.493 GB/s'],
        ];
    }
}
