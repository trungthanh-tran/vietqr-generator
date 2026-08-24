# Changelog

All notable changes to this project are documented in this file.
Versions follow the release process described in [RELEASING.md](RELEASING.md);
each release is an annotated git tag `vX.Y` that Packagist picks up automatically.

## v0.8 - 2026-08-24

### Added
- Static QR support (point of initiation method `11`) per the NAPAS VietQR
  specification: produced automatically when neither amount nor payment
  reference is set. A QR carrying an amount is now always dynamic (`12`).
- Optional merchant fields: merchant category code (field 52), merchant name
  (field 59) and merchant city (field 60), encoded in EMVCo tag order.
- Input validation against the specification limits: account number <= 19,
  amount <= 13, purpose of transaction <= 25, merchant name <= 25,
  merchant city <= 15 characters, MCC exactly 4 digits.

### Changed
- Reorganized the codebase: specification values (NAPAS GUID, currency,
  country, initiation methods, service codes, length limits) centralized in
  `Constants`; payload building extracted into `Generator::buildPayload()`;
  all comments rewritten in English.
- `generate_image()` deprecated in favor of `generateImage()` (the old name
  still works).

### Fixed
- Invalid amount now returns a 400 response instead of being silently ignored.
- Removed the unused and broken `TransferInfo` class.

## v0.7 - 2022-03-07
- Added labels rendered below the QR image.
- Updated bank list and dependencies.

## v0.6 - 2022-03-03
- Padded the CRC checksum to 4 characters when shorter.

## v0.5 - 2022-03-03
- Added unit tests for the QR generator.

## v0.4 - 2022-03-03
- Fixed QR generation issues in some cases.

## v0.3 - 2022-03-02
- Added `getBanksList()` to retrieve the supported bank list.

## v0.2 - 2022-02-28
- Added base64 PNG image generation.

## v0.1 - 2022-02-28
- Initial release: VietQR payload text generation.
