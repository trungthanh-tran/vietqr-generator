<?php

namespace tttran\viet_qr_generator;

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;
use Endroid\QrCode\Label\Alignment\LabelAlignmentCenter;
use Endroid\QrCode\Label\Font\NotoSans;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;
use Endroid\QrCode\Writer\PngWriter;

class Generator
{
    // Bank ID
    private $bankId;
    //  Account No
    private $accountNo;
    // Amount to transfer
    private $amount;
    // Ref
    private $info;
    // Return text or image in base64
    private $returnText = true;
    // Size of QR. Default 200px
    private $size = 200;
    // Size of margin. Default 10 px.
    private $margin = 10;
    // Logo path
    private $logoPath;
    // Logo width in px
    private $logoWidth = 50;
    // Logo height in px
    private $logoHeight = 50;
    // Data path
    private $data;
    // Bank tranfer by card id
    private $isCard = false;

    public static function create(): Generator
    {
        return new self();
    }

    public function __construct()
    {
        return $this;
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

    /**
     * Set the value of logoWidth
     */
    public function setLogoWidth($logoWidth): self
    {
        $this->logoWidth = $logoWidth;

        return $this;
    }


    /**
     * Set the value of logoHeight
     */
    public function setLogoHeight($logoHeight): self
    {
        $this->logoHeight = $logoHeight;

        return $this;
    }

    public function generate(): string
    {
        if (empty($this->bankId) || empty($this->accountNo)) {
            return json_encode(new Response(Response::INVALID_PARAMETERS, "Missing or invalid parameter", ""));
        }

        try {
            $stringToGenerate = "";
            $paymentType = "11";
            $consumerInfo = Helper::generateMerchantInfo($this->bankId, $this->accountNo, $this->isCard);
            // Add header
            $stringToGenerate = Helper::addField($stringToGenerate, VietQRField::VERSION, "01");
            if (!empty($this->info)) {
                $paymentType = "12";
            }
            // Payment type. 11 if permantly. 12 otherwise
            $stringToGenerate = Helper::addField($stringToGenerate, VietQRField::INITIATION_METHOD, $paymentType);
            // Add consumer info
            $stringToGenerate = Helper::addField($stringToGenerate, VietQRField::CONSUMER_INFO, $consumerInfo);
            // Add currency
            $stringToGenerate = Helper::addField($stringToGenerate, VietQRField::CURRENCY_CODE, "704");
            if (!empty($this->amount)) {
                if (!Helper::isValidAmount($this->amount)) {
                    json_encode(new Response(Response::INVALID_PARAMETERS, "Invalid amount", ""));
                }
                // Add amount
                $stringToGenerate = Helper::addField($stringToGenerate, VietQRField::TRANSACTION_AMOUNT, $this->amount);
            }
            $stringToGenerate = Helper::addField($stringToGenerate, VietQRField::COUNTRY_CODE, "VN");
            if (!empty($this->info)) {
                $ref = Helper::addField("", VietQRField::ADDITION_REF, $this->info);
                $stringToGenerate = Helper::addField($stringToGenerate, VietQRField::ADDITION, $ref);
            }
            $crc = CRCHelper::crcChecksum($stringToGenerate . VietQRField::CRC . "04");
            $crc = str_pad($crc, 4, "0", STR_PAD_LEFT);
            $this->data = Helper::addField($stringToGenerate, VietQRField::CRC, $crc);
        } catch (InvalidBankIdException $e) {
            return json_encode(new Response(Response::INVALID_PARAMETERS, "Missing or invalid bankId", ""));
        }
        if ($this->returnText) {
            return json_encode(new Response(Response::SUCCESSFUL_CODE, "ok", $this->data));
        } else {
            return json_encode(new Response(Response::SUCCESSFUL_CODE, "ok", $this->generate_image()));
        }
    }

    public function generate_image(): string
    {
        $result = Builder::create()
            ->writer(new PngWriter())
            ->writerOptions([])
            ->data($this->data)
            ->encoding(new Encoding('UTF-8'))
            ->errorCorrectionLevel(new ErrorCorrectionLevelHigh())
            ->size($this->size)
            ->margin($this->margin)
            ->roundBlockSizeMode(new RoundBlockSizeModeMargin())
            ->labelText($this->accountNo)
            ->labelFont(new NotoSans(2))
            ->labelAlignment(new LabelAlignmentCenter());
        if (!empty($this->logoPath)) {
            $result = $result->logoPath($this->logoPath)
                ->logoResizeToHeight($this->logoHeight)
                ->logoResizeToWidth($this->logoWidth);
        }
        $result = $result->build();
        return $result->getDataUri();
    }

    /**
     * Get bank list
     * @return string
     */
    public static function getBanksList(): string {
        $data = Helper::loadDataBanks();
        return json_encode(new Response(Response::SUCCESSFUL_CODE, "ok", Helper::loadDataBanks()));
    }
}