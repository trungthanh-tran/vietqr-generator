<?php
namespace tttran\VietQrGenerator;

use Helper;
use tttran\VietQrGenerator\exception\InvalidBankIdException;

class VietQRGenerator {
    public static function generate($bankId, $accountNo) {
        $stringToGenerate = '';
        try {
            $crc = '';
            $stringToGenerate = VietQRGenerator::generate_common($bankId, $accountNo);
            $crc = CRCHelper::crcChecksum($stringToGenerate);
            $stringToGenerate = $stringToGenerate.$crc;
        } catch (InvalidBankIdException $e) {
            echo "Cannot check VietQR";
        }
        return $stringToGenerate;
    }

    public static function generate_withInfo($bankId, $accountNo, $transferInfo)
    {
        $stringToGenerate = '';
        try {
            $crc = '';
            $stringToGenerate = VietQRGenerator::generate_common($bankId, $accountNo);
            
            $crc = CRCHelper::crcChecksum($stringToGenerate);
            $stringToGenerate = $stringToGenerate.$crc;
        } catch (InvalidBankIdException $e) {
            echo "Cannot check VietQR";
        }
        return $stringToGenerate;
    }

    public static function  generate_common($bankId, $accountNo) {
        $stringToGenerate = '';
        try {
            $consumerInfo = Helper::generateMerchantInfo($bankId, $accountNo);
            $stringToGenerate = Helper::addField($stringToGenerate, VietQRField::VERSION, "01");
            $stringToGenerate = Helper::addField($stringToGenerate, VietQRField::INITIATION_METHOD, "11");
            $stringToGenerate = Helper::addField($stringToGenerate, VietQRField::CONSUMER_INFO, $consumerInfo);
            $stringToGenerate = Helper::addField($stringToGenerate, VietQRField::CURRENCY_CODE, "704");
            $stringToGenerate = Helper::addField($stringToGenerate, VietQRField::COUNTRY_CODE, "VN");
        } catch (InvalidBankIdException $e) {
            throw $e;
        }
        return $stringToGenerate;
    }
}

echo VietQRGenerator::generate("vcb", "11111");