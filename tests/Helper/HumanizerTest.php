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

    /**
     * @dataProvider bytesProvider
     *
     * @param $bytes
     * @param $expected
     */
    public function testBytes($bytes, $expected)
    {
        $this->assertEquals($expected, Humanizer::bytes($bytes));
    }

    public function bytesProvider()
    {
        return [
            [32, '32 B'],
            [982345, '959.321 KB'],
            [823456789, '785.310 MB'],
            [823456789123, '766.904 GB'],
            [111193456789123, '101.130 TB'],
            [11111193456789123, '9.869 PB'],
            [11111111193456789123, '9868.649 PB'],
        ];
    }
}
