<?php

namespace tttran\viet_qr_generator;

use tttran\viet_qr_generator\InvalidBankIdException;
use tttran\viet_qr_generator\VietQRField;

class Helper {
    public static function addField($currentString, $code, $value) {
        $newValue = $currentString;
        if (empty($newValue)) {
            $newValue = '';
        }
        $newValue = $newValue . $code . sprintf("%02d", strlen($value)) . $value;
        return $newValue;
    }

    public static function generateMerchantInfo($bankId, $accountNo) {
        $merchantInfo = '';
        $receiverInfo = '';
        $binCode = '';
        try {
            $binCode = Helper::getBIN($bankId);
        } catch (InvalidBankIdException $e) {
            throw $e;
        }
        $receiverInfo = Helper::addField($receiverInfo, VietQRField::CONSUMER_INFO_CONSUMER_BIN, $binCode);
        $receiverInfo = Helper::addField($receiverInfo, VietQRField::CONSUMER_INFO_CONSUMER_MERCHANT, $accountNo);

        $merchantInfo = Helper::addField($merchantInfo, VietQRField::CONSUMER_INFO_GUID, "A000000727");
        $merchantInfo = Helper::addField($merchantInfo, VietQRField::CONSUMER_INFO_CONSUMER, $receiverInfo);
        $merchantInfo = Helper::addField($merchantInfo, VietQRField::CONSUMER_INFO_SERVICE_CODE, "QRIBFTTA");

        return $merchantInfo;
    }

    private static function getBIN($bankId) {
        if (empty($bankId)) {
            throw new InvalidBankIdException();
        }
        $bankId = strtolower($bankId);
        switch($bankId) {
            case "vietinbank":
            case "icb":
            case "970415":
                return "970415";
            case "vcb":
            case "vietcombank":
            case "970436":
                return "970436";
            case "mb":
            case "mbbank":
            case "970422":
                return "970422";
            case "acb":
            case "970416":
                return "970416";
            case "vpb":
            case "vpbank":
            case "970432":
                return "970432";
            case "msb":
            case "970426":
                return "970426";
            case "bidv":
            case "970418":
                return "970418";
            case "stb":
            case "sacombank":
            case "970403":
                return "970403";
            default:
                throw new InvalidBankIdException();
        }
    }

    public static function isValidAmount($amount): bool {
        $regExpPattern = '/^\d{1,}\.?\d{0,2}$/';
        $currencyToTest = trim ($amount);
        return preg_match ($regExpPattern, $currencyToTest);
    }
}
