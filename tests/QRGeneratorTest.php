<?php

namespace tttran\viet_qr_generator\tests;

use PHPUnit\Framework\TestCase;
use tttran\viet_qr_generator\Generator;

final class QRGeneratorTest extends TestCase
{
    public function test_generate_text_data_vcb(): void
    {
        $gen = new Generator();
        $res = $gen->bankId("vietcombank")
            ->accountNo("1016039126")
            ->amount(500000)
            ->info("65498")
            ->generate();

        $data = json_decode($res, true);

        $expectedValue = '00020101021238540010A00000072701240006970436011010160391260208QRIBFTTA530370454065000005802VN620908056549863046206';

        $this->assertEquals($data['code'], 200);
        $this->assertEquals($data['data'], $expectedValue);
    }

    public function test_generate_text_data_mbbank(): void
    {
        $gen = new Generator();
        $res = $gen->bankId("mbbank")
            ->accountNo("6850180919999")
            ->amount(500000)
            ->info("65503")
            ->generate();

        $data = json_decode($res, true);

        $expectedValue = '00020101021238570010A00000072701270006970422011368501809199990208QRIBFTTA530370454065000005802VN62090805655036304D97D';

        $this->assertEquals($data['code'], 200);
        $this->assertEquals($data['data'], $expectedValue);
    }

    public function test_generate_text_data_vpbank(): void
    {
        $gen = new Generator();
        $res = $gen->bankId("vpbank")
            ->accountNo("222892171")
            ->amount(500000)
            ->info("65504")
            ->generate();

        $data = json_decode($res, true);

        $expectedValue = '00020101021238530010A0000007270123000697043201092228921710208QRIBFTTA530370454065000005802VN62090805655046304CFCA';

        $this->assertEquals($data['code'], 200);
        $this->assertEquals($data['data'], $expectedValue);
    }

    public function test_generate_text_data_acb(): void
    {
        $gen = new Generator();
        $res = $gen->bankId("acb")
            ->accountNo("14407457")
            ->amount(500000)
            ->info("65506")
            ->generate();

        $data = json_decode($res, true);

        $expectedValue = '00020101021238520010A000000727012200069704160108144074570208QRIBFTTA530370454065000005802VN620908056550663041957';

        $this->assertEquals($data['code'], 200);
        $this->assertEquals($data['data'], $expectedValue);
    }

    public function test_generate_text_data_bidv(): void
    {
        $gen = new Generator();
        $res = $gen->bankId("bidv")
            ->accountNo("21510003078427")
            ->amount(500000)
            ->info("65508")
            ->generate();

        $data = json_decode($res, true);

        $expectedValue = '00020101021238580010A000000727012800069704180114215100030784270208QRIBFTTA530370454065000005802VN62090805655086304E0C9';

        $this->assertEquals($data['code'], 200);
        $this->assertEquals($data['data'], $expectedValue);
    }

    public function test_generate_text_data_sacombank(): void
    {
        $gen = new Generator();
        $res = $gen->bankId("sacombank")
            ->accountNo("020090552091")
            ->amount(500000)
            ->info("65510")
            ->generate();

        $data = json_decode($res, true);

        $expectedValue = '00020101021238560010A0000007270126000697040301120200905520910208QRIBFTTA530370454065000005802VN62090805655106304EBB8';

        $this->assertEquals($data['code'], 200);
        $this->assertEquals($data['data'], $expectedValue);
    }

    public function test_generate_static_qr(): void
    {
        // Static QR: no amount and no reference, initiation method must be "11".
        $gen = new Generator();
        $res = $gen->bankId("vietcombank")
            ->accountNo("1016039126")
            ->generate();

        $data = json_decode($res, true);

        $expectedValue = '00020101021138540010A00000072701240006970436011010160391260208QRIBFTTA53037045802VN6304EE69';

        $this->assertEquals($data['code'], 200);
        $this->assertEquals($data['data'], $expectedValue);
    }

    public function test_generate_dynamic_qr_with_amount_only(): void
    {
        // A QR carrying an amount is dynamic ("12") even without a reference.
        $gen = new Generator();
        $res = $gen->bankId("vietcombank")
            ->accountNo("1016039126")
            ->amount(10000)
            ->generate();

        $data = json_decode($res, true);

        $this->assertEquals($data['code'], 200);
        $this->assertEquals(substr($data['data'], 0, 12), '000201010212');
    }

    public function test_generate_invalid_amount(): void
    {
        $gen = new Generator();
        $res = $gen->bankId("vietcombank")
            ->accountNo("1016039126")
            ->amount("12.345")
            ->generate();

        $data = json_decode($res, true);

        $this->assertEquals($data['code'], 400);
    }

    public function test_generate_with_merchant_fields(): void
    {
        // MCC (52), merchant name (59) and city (60) are encoded in tag order.
        $gen = new Generator();
        $res = $gen->bankId("vietcombank")
            ->accountNo("1016039126")
            ->merchantCategoryCode("5812")
            ->merchantName("CUA HANG ABC")
            ->merchantCity("HANOI")
            ->amount(10000)
            ->info("order 1")
            ->generate();

        $data = json_decode($res, true);

        $expectedValue = '00020101021238540010A00000072701240006970436011010160391260208QRIBFTTA5204581253037045405100005802VN5912CUA HANG ABC6005HANOI62110807order 16304A2DE';

        $this->assertEquals($data['code'], 200);
        $this->assertEquals($data['data'], $expectedValue);
    }

    public function test_length_validations(): void
    {
        $tooLongAccount = json_decode((new Generator())->bankId("vcb")->accountNo(str_repeat("1", 20))->generate(), true);
        $this->assertEquals($tooLongAccount['code'], 400);

        $tooLongPurpose = json_decode((new Generator())->bankId("vcb")->accountNo("1016039126")->info(str_repeat("a", 26))->generate(), true);
        $this->assertEquals($tooLongPurpose['code'], 400);

        $tooLongAmount = json_decode((new Generator())->bankId("vcb")->accountNo("1016039126")->amount("12345678901234")->generate(), true);
        $this->assertEquals($tooLongAmount['code'], 400);

        $invalidMcc = json_decode((new Generator())->bankId("vcb")->accountNo("1016039126")->merchantCategoryCode("123")->generate(), true);
        $this->assertEquals($invalidMcc['code'], 400);

        $tooLongName = json_decode((new Generator())->bankId("vcb")->accountNo("1016039126")->merchantName(str_repeat("a", 26))->generate(), true);
        $this->assertEquals($tooLongName['code'], 400);

        $tooLongCity = json_decode((new Generator())->bankId("vcb")->accountNo("1016039126")->merchantCity(str_repeat("a", 16))->generate(), true);
        $this->assertEquals($tooLongCity['code'], 400);

        // Boundary values are accepted.
        $boundary = json_decode((new Generator())->bankId("vcb")->accountNo(str_repeat("1", 19))->info(str_repeat("a", 25))->generate(), true);
        $this->assertEquals($boundary['code'], 200);
    }

    public function test_generate_base64_data(): void
    {
        $gen = new Generator();
        $res = $gen->bankId("vietcombank")
            ->accountNo("1016039126")
            ->amount(500000)
            ->info("65499")
            ->returnText(false)
            ->generate();

        $data = json_decode($res, true);

        $this->assertEquals($data['code'], 200);
        $this->assertNotEmpty($data['data']);
    }
}
