<?php
namespace tttran\viet_qr_generator;

class Generator {
    private $bankId;
    private $accountNo;
    private $amount;
    private $info;

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
            $stringToGenerate = Generator::generate_common($this->bankId, $this->accountNo);
            if (!empty($this->amount)) {
                $stringToGenerate = Helper::addField($stringToGenerate, VietQRField::TRANSACTION_AMOUNT, $this->amount);
            }
            if (!empty($this->info)) {
                $stringToGenerate = Helper::addField($stringToGenerate, VietQRField::ADDITION, $this->info);
            }
            $crc = CRCHelper::crcChecksum($stringToGenerate.VietQRField::CRC."04");
            $stringToGenerate = Helper::addField($stringToGenerate, VietQRField::CRC, $crc);
        } catch (InvalidBankIdException $e) {
            return json_encode(new Response(Response::INVALID_PARAMETERS, "Missing or invalid bankId", ""));
        }
        return json_encode(new Response(Response::SUCCESSFUL_CODE, "ok", $stringToGenerate));
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