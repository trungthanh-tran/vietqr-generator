<?php


namespace tttran\viet_qr_generator;


abstract class VietQRField
{
    const VERSION = "00";
    const INITIATION_METHOD = "01";
    const CONSUMER_INFO = "38";
    const CURRENCY_CODE = "53";
    const TRANSACTION_AMOUNT = "54";
    const COUNTRY_CODE = "58";
    const ADDITION = "62";
    const CRC = "63";

    const CONSUMER_INFO_GUID = "00";
    const CONSUMER_INFO_CONSUMER = "01";
    const CONSUMER_INFO_CONSUMER_BIN = "00";
    const CONSUMER_INFO_CONSUMER_MERCHANT = "01";
    const CONSUMER_INFO_SERVICE_CODE = "02";

    const ADDITION_REF = "08";
}