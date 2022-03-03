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
