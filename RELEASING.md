# Release process

This project follows a Maven-like release flow (`release:prepare` /
`release:perform`), adapted to Composer: Packagist derives package versions
from annotated git tags, so a release is a changelog entry plus a tag —
`composer.json` must NOT contain a `version` field.

## Steps

1. **Prepare** — make sure the working tree is clean and tests pass:
   ```bash
   composer install
   vendor/bin/phpunit tests
   ```
2. **Update the changelog** — add a `## vX.Y - YYYY-MM-DD` section on top of
   [CHANGELOG.md](CHANGELOG.md) describing the release, and commit it.
3. **Tag and push** — run the release script, which verifies the tree is
   clean, the changelog contains the version, then creates an annotated tag
   and pushes it:
   ```bash
   ./scripts/release.sh 0.8
   ```
   Or manually:
   ```bash
   git tag -a v0.8 -m "Release v0.8"
   git push origin main v0.8
   ```
4. **Perform** — Packagist picks the new tag up automatically (or via the
   "Update" button on the package page). Verify the new version appears at
   https://packagist.org/packages/tttran/viet_qr_generator.

## Versioning

- `vX.Y` tags, following the existing scheme (v0.1 ... v0.8).
- Bump `Y` for features and fixes; bump `X` for breaking API changes.
