<?php
namespace tttran\viet_qr_generator;

class Generator {
    private $bankId;
    private $accountNo;
    private $amount;
    private $info;

    public static function create(): Generator {
        return new self();
    }

    public function __construct()
    {
        return $this;
    }

    public function bankId($bankId) {
        $this->bankId = $bankId;
        return $this;
    }

    public function accountNo($accountNo) {
        $this->accountNo = $accountNo;
        return $this;
    }

    public function amount($amount) {
        $this->amount = $amount;
        return $this;
    }

    public function info($info) {
        $this->info = $info;
        return $this;
    }

    public function generate() {
        if (empty($this->bankId) || empty($this->accountNo))
        {
            return json_encode(new Response(Response::INVALID_PARAMETERS, "Missing or invalid parameter", ""));
        }

        try {
            $stringToGenerate = "";
            $paymentType = "11";
            $consumerInfo = Helper::generateMerchantInfo($this->bankId, $this->accountNo);
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
            $crc = CRCHelper::crcChecksum($stringToGenerate.VietQRField::CRC."04");
            $stringToGenerate = Helper::addField($stringToGenerate, VietQRField::CRC, $crc);
        } catch (InvalidBankIdException $e) {
            return json_encode(new Response(Response::INVALID_PARAMETERS, "Missing or invalid bankId", ""));
        }
        return json_encode(new Response(Response::SUCCESSFUL_CODE, "ok", $stringToGenerate));
    }
}