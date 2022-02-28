<?php

namespace tttran\viet_qr_generator\tests;

use PHPUnit\Framework\TestCase;
use tttran\viet_qr_generator\Generator;

final class QRGeneratorTest extends TestCase
{
    public function testVCB(): void
    {
        $gen = new Generator();
        $gen->bankId("vietcombank");
        $gen->accountNo("111111");
        $gen->amount(10000);
        $gen->info("toto");
        $this->assertEquals($gen, 1);
    }
}