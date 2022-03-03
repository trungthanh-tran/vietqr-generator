<?php

namespace tttran\viet_qr_generator;

class Helper
{
    private static $banks;
    private static $json_banks;
    public static function addField(string $currentString, string $code, string $value): string
    {
        $newValue = $currentString;
        if (empty($newValue)) {
            $newValue = '';
        }
        $newValue = $newValue . $code . sprintf("%02d", strlen($value)) . $value;
        return $newValue;
    }

    public static function generateMerchantInfo(string $bankId, string $accountNo, bool $isAccount): string
    {
        $merchantInfo = '';
        $receiverInfo = '';
        $serviceCode = Helper::getNapasServiceCode($isAccount);
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
        $merchantInfo = Helper::addField($merchantInfo, VietQRField::CONSUMER_INFO_SERVICE_CODE, $serviceCode);

        return $merchantInfo;
    }

    /**
     * Get bin code
     * @param string $bankId/bin/shortname
     * @return string
     * @throws InvalidBankIdException
     */
    private static function getBIN(string $bankId): string
    {
        if (empty($bankId)) {
            throw new InvalidBankIdException();
        }
        $bankId = strtolower($bankId);
        if (empty(self::$banks)) {
            $bankData = self::loadDataBanks();
            self::$banks = array();
            foreach ($bankData["data"] as $item) {
                self::$banks[strtolower($item["code"])] = strtolower($item["bin"]);
                self::$banks[strtolower($item["bin"])] = strtolower($item["bin"]);
                self::$banks[strtolower($item["short_name"])] = strtolower($item["bin"]);
            }
        }
        if (isset(self::$banks[$bankId])) {
            return self::$banks[$bankId];
        } else {
            throw new InvalidBankIdException();
        }
    }

    public static function isValidAmount(int $amount): bool
    {
        $regExpPattern = '/^\d{1,}\.?\d{0,2}$/';
        $currencyToTest = trim($amount);
        return preg_match($regExpPattern, $currencyToTest);
    }

    public static function getNapasServiceCode(bool $isCard): string
    {
        if ($isCard) {
            return Constants::NAPAS_247_BY_CARD;
        } else {
            return Constants::NAPAS_247_BY_ACCOUNT;
        }
    }

    public static function loadDataBanks() {
        if (!isset(self::$json_banks)) {
            $banks = file_get_contents(__DIR__ . '/conf/banks.json');
            self::$json_banks =  json_decode($banks,true);
        }
        return self::$json_banks;
    }
}
