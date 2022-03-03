# Introduction 

Extension to generate Text according to VietQR Specification via account.

# Import lib
```json
{
  "require":{
    "tttran/viet_qr_generator": "v0.6"
  }
}

```
# Examples
##Generate with amount
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


## Generate permanently bank transfer
```php
use tttran\viet_qr_generator\Generator;

        $gen = Generator()::create()
            ->bankId("VCB") // BankId, bankname
            ->accountNo("111111")// Account number
            ->generate();
        echo $gen; // Print text to generate QR Code
```


## Generate base64 image
```php
use tttran\viet_qr_generator\Generator;

        $gen = Generator()::create()
            ->bankId("VCB") // BankId, bankname
            ->accountNo("111111")// Account number
            ->amount(10000)// Money
            ->info("toto") // Ref
            ->returnText(false) // if true, return text. If false, return image in base64
            ->generate();
        $result = json_decode($gen->generate()); // Print text to generate QR Code
        echo $result->data; // image in base64
```

# Ref:

Functions

| Field | Type of parameters | Meaning |
| --- | --- | --- |
| bankId | String | Bank ID |
| accountNo| String |  Bank Account
| amount | number with only one dot or not | Amount to transfer. 1000. or 1000
| info | String |  Ref |
| returnText | bool | return text if true. Otherwise, return base 64
|  size | integer | size of QR in pixel |
| margin | integer | margin of QR |
| logoPath | String | Path to logo in the center of image |
| isCard | bool | True when bank transfer via card no. False via account no. Default: false |
