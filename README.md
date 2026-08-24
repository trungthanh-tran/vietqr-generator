# Introduction

Library to generate QR payloads according to the VietQR (NAPAS 247) specification, as text or as a base64 PNG image.

# Installation
```json
{
  "require": {
    "tttran/viet_qr_generator": "v0.8"
  }
}
```

# Examples

## Dynamic QR with amount and reference
A QR that carries the amount and/or a payment reference is a **dynamic QR** (point of initiation method `12`) and is meant for a single transaction.
```php
use tttran\viet_qr_generator\Generator;

$gen = Generator::create()
    ->bankId("VCB")        // Bank code, BIN or short name
    ->accountNo("111111")  // Account number
    ->amount(10000)        // Amount to transfer
    ->info("toto")         // Payment reference
    ->generate();
echo $gen; // JSON response whose data is the QR payload text
```

## Static QR
A QR without amount and reference is a **static QR** (point of initiation method `11`): it can be printed once and reused for many transactions, the payer fills in the amount and message.
```php
use tttran\viet_qr_generator\Generator;

$gen = Generator::create()
    ->bankId("VCB")        // Bank code, BIN or short name
    ->accountNo("111111")  // Account number
    ->generate();
echo $gen; // JSON response whose data is the QR payload text
```

## Generate a base64 image
```php
use tttran\viet_qr_generator\Generator;

$gen = Generator::create()
    ->bankId("VCB")        // Bank code, BIN or short name
    ->accountNo("111111")  // Account number
    ->amount(10000)        // Amount to transfer
    ->info("toto")         // Payment reference
    ->returnText(false)    // false: return a base64 PNG instead of text
    ->generate();
$result = json_decode($gen);
echo $result->data; // Image as a base64 data URI
```

# Reference

| Method | Parameter type | Meaning |
| --- | --- | --- |
| bankId | string | Bank code, BIN or short name |
| accountNo | string | Bank account number (or card number when isCard is true). Max 19 characters |
| amount | number, at most two decimals | Amount to transfer, e.g. 1000 or 1000.50. Max 13 characters. Omit for a static QR |
| info | string | Payment reference. Max 25 characters. Omit for a static QR |
| merchantCategoryCode | string | Optional merchant category code (ISO 18245), exactly 4 digits |
| merchantName | string | Optional merchant name. Max 25 characters |
| merchantCity | string | Optional merchant city. Max 15 characters |
| returnText | bool | true: return payload text. false: return a base64 PNG image |
| size | int | QR image size in pixels |
| margin | int | QR image margin in pixels |
| logoPath | string | Path to a logo drawn at the center of the image |
| isCard | bool | true: transfer by card number. false (default): by account number |
