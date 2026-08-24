<?php

namespace tttran\viet_qr_generator;

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;
use Endroid\QrCode\Label\LabelInterface;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;
use Endroid\QrCode\Writer\PngWriter;

/**
 * Fluent builder that generates VietQR (NAPAS 247) payloads following the
 * VietQR specification, either as raw EMVCo text or as a base64 PNG image.
 *
 * Static vs dynamic QR:
 * - Static QR (initiation method "11"): only bank and account are encoded.
 *   The code can be printed once and reused; the payer fills in the amount
 *   and message. Produced when neither amount() nor info() is set.
 * - Dynamic QR (initiation method "12"): the code carries the amount and/or
 *   the payment reference and is meant for a single transaction. Produced
 *   automatically when amount() or info() is set.
 */
class Generator
{
    /** Bank identifier: code, BIN or short name (see conf/banks.json). */
    private $bankId;

    /** Beneficiary account number (or card number when isCard is true). */
    private $accountNo;

    /** Amount to transfer. Leave empty for a static QR. */
    private $amount;

    /** Payment reference / message. Leave empty for a static QR. */
    private $info;

    /** Merchant category code (ISO 18245, 4 digits). Optional. */
    private $merchantCategoryCode;

    /** Merchant name (field 59). Optional. */
    private $merchantName;

    /** Merchant city (field 60). Optional. */
    private $merchantCity;

    /** When true, return the QR payload as text; otherwise a base64 PNG image. */
    private $returnText = true;

    /** QR image size in pixels. */
    private $size = 300;

    /** QR image margin in pixels. */
    private $margin = 10;

    /** Path to a logo drawn at the center of the QR image. */
    private $logoPath;

    /** Logo width in pixels. */
    private $logoWidth = 50;

    /** Logo height in pixels. */
    private $logoHeight = 50;

    /** Generated payload text. */
    private $data;

    /** True to transfer by card number, false by account number. */
    private $isCard = false;

    /** Labels rendered below the QR image. */
    private $labels;

    public static function create(): Generator
    {
        return new self();
    }

    public function bankId($bankId): Generator
    {
        $this->bankId = $bankId;
        return $this;
    }

    public function accountNo($accountNo): Generator
    {
        $this->accountNo = $accountNo;
        return $this;
    }

    public function amount($amount): Generator
    {
        $this->amount = $amount;
        return $this;
    }

    public function info($info): Generator
    {
        $this->info = $info;
        return $this;
    }

    public function merchantCategoryCode($merchantCategoryCode): Generator
    {
        $this->merchantCategoryCode = $merchantCategoryCode;
        return $this;
    }

    public function merchantName($merchantName): Generator
    {
        $this->merchantName = $merchantName;
        return $this;
    }

    public function merchantCity($merchantCity): Generator
    {
        $this->merchantCity = $merchantCity;
        return $this;
    }

    public function returnText($returnText): Generator
    {
        $this->returnText = $returnText;
        return $this;
    }

    public function size($size): Generator
    {
        $this->size = $size;
        return $this;
    }

    public function margin($margin): Generator
    {
        $this->margin = $margin;
        return $this;
    }

    public function logoPath($logoPath): Generator
    {
        $this->logoPath = $logoPath;
        return $this;
    }

    public function isCard(bool $isCard): Generator
    {
        $this->isCard = $isCard;
        return $this;
    }

    public function setLogoWidth($logoWidth): self
    {
        $this->logoWidth = $logoWidth;
        return $this;
    }

    public function setLogoHeight($logoHeight): self
    {
        $this->logoHeight = $logoHeight;
        return $this;
    }

    /**
     * Add a label rendered below the QR image.
     */
    public function addLabel(LabelInterface $label): self
    {
        $this->labels[] = $label;
        return $this;
    }

    /**
     * Generate the VietQR payload.
     *
     * @return string JSON-encoded Response whose data is the payload text
     *                (returnText = true) or a base64 PNG data URI.
     */
    public function generate(): string
    {
        $validationError = $this->validate();
        if ($validationError !== null) {
            return json_encode(new Response(Response::INVALID_PARAMETERS, $validationError, ""));
        }

        try {
            $this->data = $this->buildPayload();
        } catch (InvalidBankIdException $e) {
            return json_encode(new Response(Response::INVALID_PARAMETERS, "Missing or invalid bankId", ""));
        }

        $data = $this->returnText ? $this->data : $this->generateImage();
        return json_encode(new Response(Response::SUCCESSFUL_CODE, "ok", $data));
    }

    /**
     * Validate the configured inputs against the VietQR specification limits.
     *
     * @return string|null Error message, or null when everything is valid
     */
    private function validate()
    {
        if (empty($this->bankId) || empty($this->accountNo)) {
            return "Missing or invalid parameter";
        }
        if (strlen($this->accountNo) > Constants::MAX_LENGTH_ACCOUNT_NO) {
            return "Account number exceeds " . Constants::MAX_LENGTH_ACCOUNT_NO . " characters";
        }
        if (!empty($this->amount)) {
            if (!Helper::isValidAmount($this->amount)) {
                return "Invalid amount";
            }
            if (strlen((string) $this->amount) > Constants::MAX_LENGTH_AMOUNT) {
                return "Amount exceeds " . Constants::MAX_LENGTH_AMOUNT . " characters";
            }
        }
        if (!empty($this->info) && strlen($this->info) > Constants::MAX_LENGTH_PURPOSE) {
            return "Purpose of transaction exceeds " . Constants::MAX_LENGTH_PURPOSE . " characters";
        }
        if (!empty($this->merchantCategoryCode) && !preg_match('/^\d{4}$/', (string) $this->merchantCategoryCode)) {
            return "Merchant category code must be exactly " . Constants::LENGTH_MCC . " digits";
        }
        if (!empty($this->merchantName) && strlen($this->merchantName) > Constants::MAX_LENGTH_MERCHANT_NAME) {
            return "Merchant name exceeds " . Constants::MAX_LENGTH_MERCHANT_NAME . " characters";
        }
        if (!empty($this->merchantCity) && strlen($this->merchantCity) > Constants::MAX_LENGTH_MERCHANT_CITY) {
            return "Merchant city exceeds " . Constants::MAX_LENGTH_MERCHANT_CITY . " characters";
        }
        return null;
    }

    /**
     * Build the EMVCo payload text, including the trailing CRC field.
     *
     * @return string
     * @throws InvalidBankIdException
     */
    private function buildPayload(): string
    {
        $payload = Helper::addField('', VietQRField::VERSION, Constants::PAYLOAD_FORMAT_VERSION);
        $payload = Helper::addField($payload, VietQRField::INITIATION_METHOD, $this->getInitiationMethod());
        $payload = Helper::addField($payload, VietQRField::CONSUMER_INFO, Helper::generateMerchantInfo($this->bankId, $this->accountNo, $this->isCard));
        if (!empty($this->merchantCategoryCode)) {
            $payload = Helper::addField($payload, VietQRField::MERCHANT_CATEGORY_CODE, $this->merchantCategoryCode);
        }
        $payload = Helper::addField($payload, VietQRField::CURRENCY_CODE, Constants::CURRENCY_VND);
        if (!empty($this->amount)) {
            $payload = Helper::addField($payload, VietQRField::TRANSACTION_AMOUNT, $this->amount);
        }
        $payload = Helper::addField($payload, VietQRField::COUNTRY_CODE, Constants::COUNTRY_VN);
        if (!empty($this->merchantName)) {
            $payload = Helper::addField($payload, VietQRField::MERCHANT_NAME, $this->merchantName);
        }
        if (!empty($this->merchantCity)) {
            $payload = Helper::addField($payload, VietQRField::MERCHANT_CITY, $this->merchantCity);
        }
        if (!empty($this->info)) {
            $additionalData = Helper::addField('', VietQRField::ADDITION_REF, $this->info);
            $payload = Helper::addField($payload, VietQRField::ADDITION, $additionalData);
        }

        // The CRC covers the whole payload including its own field ID and length.
        $crc = CRCHelper::crcChecksum($payload . VietQRField::CRC . "04");
        $crc = str_pad($crc, 4, "0", STR_PAD_LEFT);
        return Helper::addField($payload, VietQRField::CRC, $crc);
    }

    /**
     * Resolve the point of initiation method: dynamic when the QR carries
     * an amount or a payment reference, static otherwise.
     *
     * @return string
     */
    private function getInitiationMethod(): string
    {
        if (!empty($this->amount) || !empty($this->info)) {
            return Constants::INITIATION_METHOD_DYNAMIC;
        }
        return Constants::INITIATION_METHOD_STATIC;
    }

    /**
     * Render the generated payload as a PNG image.
     *
     * @return string Base64 data URI
     */
    public function generateImage(): string
    {
        $builder = Builder::create()
            ->writer(new PngWriter())
            ->writerOptions([])
            ->data($this->data)
            ->encoding(new Encoding('UTF-8'))
            ->errorCorrectionLevel(new ErrorCorrectionLevelHigh())
            ->size($this->size)
            ->margin($this->margin)
            ->roundBlockSizeMode(new RoundBlockSizeModeMargin());

        foreach (($this->labels ?? []) as $label) {
            $builder->addLabel($label->getText(), $label->getFont(), $label->getAlignment(), $label->getMargin(), $label->getTextColor());
        }

        if (!empty($this->logoPath)) {
            $builder = $builder->logoPath($this->logoPath)
                ->logoResizeToHeight($this->logoHeight)
                ->logoResizeToWidth($this->logoWidth);
        }

        return $builder->build()->getDataUri();
    }

    /**
     * @deprecated Use generateImage() instead.
     */
    public function generate_image(): string
    {
        return $this->generateImage();
    }

    /**
     * Get the supported bank list.
     *
     * @return string JSON-encoded Response
     */
    public static function getBanksList(): string
    {
        return json_encode(new Response(Response::SUCCESSFUL_CODE, "ok", Helper::loadDataBanks()));
    }
}
