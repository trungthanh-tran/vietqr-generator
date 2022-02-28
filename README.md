Extension to generate Text according to VietQR Specification via account.

Generate with amount
```php
use tttran\viet_qr_generator\Generator;

        $gen = Generator()::create()
            ->bankId("VCB") // BankId, bankname
            ->accountNo("111111")// Account number
            ->amount(10000)// Money
            ->info("toto") // Ref
            ->generate();
        echo $gen; // Print text to generate QR Code
```


Generate permanently bank transfer
```php
use tttran\viet_qr_generator\Generator;

        $gen = Generator()::create()
            ->bankId("VCB") // BankId, bankname
            ->accountNo("111111")// Account number
            ->generate();
        echo $gen; // Print text to generate QR Code
```