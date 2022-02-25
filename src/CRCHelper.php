<?php


namespace tttran\VietQrGenerator;


class CRCHelper
{
    public static function crcChecksum($str) {
        function charCodeAt($str, $i) {
            return ord(substr($str, $i, 1));
        }

        $crc = 0xFFFF;
        $strlen = strlen($str);
        for($c = 0; $c < $strlen; $c++) {
            $crc ^= charCodeAt($str, $c) << 8;
            for($i = 0; $i < 8; $i++) {
                if($crc & 0x8000) {
                    $crc = ($crc << 1) ^ 0x1021;
                } else {
                    $crc = $crc << 1;
                }
            }
        }
        $hex = $crc & 0xFFFF;
        $hex = dechex($hex);
        $hex = strtoupper($hex);
        return $hex;
    }
}