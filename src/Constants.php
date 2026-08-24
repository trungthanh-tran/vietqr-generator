<?php

namespace tttran\viet_qr_generator;

/**
 * Constant values defined by the VietQR (NAPAS 247) specification.
 */
class Constants
{
    /** Payload format indicator value (EMVCo QR version). */
    const PAYLOAD_FORMAT_VERSION = "01";

    /**
     * Static QR: the same QR code is reused for multiple transactions.
     * The payer enters the amount and message manually.
     */
    const INITIATION_METHOD_STATIC = "11";

    /**
     * Dynamic QR: the QR code is generated for a single transaction
     * and already carries the amount and/or the payment reference.
     */
    const INITIATION_METHOD_DYNAMIC = "12";

    /** Globally unique identifier of NAPAS. */
    const NAPAS_GUID = "A000000727";

    /** ISO 4217 numeric code for Vietnamese Dong. */
    const CURRENCY_VND = "704";

    /** ISO 3166-1 alpha-2 country code for Vietnam. */
    const COUNTRY_VN = "VN";

    /** NAPAS 247 service code: transfer to a card number. */
    const NAPAS_247_BY_CARD = "QRIBFTTC";

    /** NAPAS 247 service code: transfer to an account number. */
    const NAPAS_247_BY_ACCOUNT = "QRIBFTTA";

    /** Maximum length of the account/card number (field 38-01-01). */
    const MAX_LENGTH_ACCOUNT_NO = 19;

    /** Maximum length of the transaction amount (field 54). */
    const MAX_LENGTH_AMOUNT = 13;

    /** Maximum length of the purpose of transaction (field 62-08). */
    const MAX_LENGTH_PURPOSE = 25;

    /** Exact length of the merchant category code (field 52). */
    const LENGTH_MCC = 4;

    /** Maximum length of the merchant name (field 59). */
    const MAX_LENGTH_MERCHANT_NAME = 25;

    /** Maximum length of the merchant city (field 60). */
    const MAX_LENGTH_MERCHANT_CITY = 15;
}
