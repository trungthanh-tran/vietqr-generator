<?php

namespace tttran\viet_qr_generator;

/**
 * EMVCo field IDs (tags) used by the VietQR specification.
 *
 * Each field in the payload is encoded as: ID (2 digits) + length (2 digits) + value.
 */
abstract class VietQRField
{
    /** Payload format indicator. */
    const VERSION = "00";
    /** Point of initiation method (static or dynamic). */
    const INITIATION_METHOD = "01";
    /** Consumer account information (NAPAS merchant info). */
    const CONSUMER_INFO = "38";
    /** Merchant category code (ISO 18245). */
    const MERCHANT_CATEGORY_CODE = "52";
    /** Transaction currency (ISO 4217 numeric). */
    const CURRENCY_CODE = "53";
    /** Transaction amount. */
    const TRANSACTION_AMOUNT = "54";
    /** Country code (ISO 3166-1 alpha-2). */
    const COUNTRY_CODE = "58";
    /** Merchant name. */
    const MERCHANT_NAME = "59";
    /** Merchant city. */
    const MERCHANT_CITY = "60";
    /** Additional data field template. */
    const ADDITION = "62";
    /** CRC checksum (always the last field). */
    const CRC = "63";

    /** Sub-fields of CONSUMER_INFO (field 38). */
    const CONSUMER_INFO_GUID = "00";
    const CONSUMER_INFO_CONSUMER = "01";
    const CONSUMER_INFO_SERVICE_CODE = "02";

    /** Sub-fields of CONSUMER_INFO_CONSUMER (field 38-01). */
    const CONSUMER_INFO_CONSUMER_BIN = "00";
    const CONSUMER_INFO_CONSUMER_MERCHANT = "01";

    /** Sub-field of ADDITION (field 62): purpose of transaction. */
    const ADDITION_REF = "08";
}
