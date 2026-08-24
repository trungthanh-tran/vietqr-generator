<?php

namespace tttran\viet_qr_generator;

/**
 * Helper functions for building the VietQR payload.
 */
class Helper
{
    /** In-memory lookup: bank code/bin/short name (lowercase) => BIN. */
    private static $banks;

    /** Cached content of conf/banks.json. */
    private static $json_banks;

    /**
     * Append an EMVCo TLV field (ID + 2-digit length + value) to the payload.
     *
     * @param string $currentString Payload built so far
     * @param string $code Two-digit field ID
     * @param string $value Field value
     * @return string Payload with the new field appended
     */
    public static function addField(string $currentString, string $code, string $value): string
    {
        return $currentString . $code . sprintf("%02d", strlen($value)) . $value;
    }

    /**
     * Build the consumer account information field (field 38) content.
     *
     * @param string $bankId Bank code, BIN or short name
     * @param string $accountNo Account number or card number
     * @param bool $isCard True to transfer by card number, false by account number
     * @return string Encoded merchant info
     * @throws InvalidBankIdException When the bank cannot be resolved
     */
    public static function generateMerchantInfo(string $bankId, string $accountNo, bool $isCard): string
    {
        $binCode = self::getBIN($bankId);

        $receiverInfo = self::addField('', VietQRField::CONSUMER_INFO_CONSUMER_BIN, $binCode);
        $receiverInfo = self::addField($receiverInfo, VietQRField::CONSUMER_INFO_CONSUMER_MERCHANT, $accountNo);

        $merchantInfo = self::addField('', VietQRField::CONSUMER_INFO_GUID, Constants::NAPAS_GUID);
        $merchantInfo = self::addField($merchantInfo, VietQRField::CONSUMER_INFO_CONSUMER, $receiverInfo);
        $merchantInfo = self::addField($merchantInfo, VietQRField::CONSUMER_INFO_SERVICE_CODE, self::getNapasServiceCode($isCard));

        return $merchantInfo;
    }

    /**
     * Resolve a bank identifier (code, BIN or short name) to its BIN.
     *
     * @param string $bankId Bank code, BIN or short name
     * @return string BIN code
     * @throws InvalidBankIdException When the bank cannot be resolved
     */
    private static function getBIN(string $bankId): string
    {
        if (empty($bankId)) {
            throw new InvalidBankIdException();
        }
        if (empty(self::$banks)) {
            $bankData = self::loadDataBanks();
            self::$banks = array();
            foreach ($bankData["data"] as $item) {
                self::$banks[strtolower($item["code"])] = strtolower($item["bin"]);
                self::$banks[strtolower($item["bin"])] = strtolower($item["bin"]);
                self::$banks[strtolower($item["short_name"])] = strtolower($item["bin"]);
            }
        }
        $bankId = strtolower($bankId);
        if (!isset(self::$banks[$bankId])) {
            throw new InvalidBankIdException();
        }
        return self::$banks[$bankId];
    }

    /**
     * Check that the amount is a positive number with at most two decimals.
     *
     * @param int|float|string $amount
     * @return bool
     */
    public static function isValidAmount($amount): bool
    {
        return (bool) preg_match('/^\d{1,}\.?\d{0,2}$/', trim((string) $amount));
    }

    /**
     * Get the NAPAS 247 service code for the transfer type.
     *
     * @param bool $isCard True to transfer by card number, false by account number
     * @return string
     */
    public static function getNapasServiceCode(bool $isCard): string
    {
        return $isCard ? Constants::NAPAS_247_BY_CARD : Constants::NAPAS_247_BY_ACCOUNT;
    }

    /**
     * Load and cache the bank list from conf/banks.json.
     *
     * @return array
     */
    public static function loadDataBanks()
    {
        if (!isset(self::$json_banks)) {
            $banks = file_get_contents(__DIR__ . '/conf/banks.json');
            self::$json_banks = json_decode($banks, true);
        }
        return self::$json_banks;
    }
}
