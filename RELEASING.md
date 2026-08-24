# Release process

This project follows the Maven release flow (`release:prepare` /
`release:perform`), adapted to Composer:

- The library version lives in [src/Version.php](src/Version.php) (the
  `pom.xml` equivalent) and carries a `-SNAPSHOT` suffix during development.
- Packagist derives package versions from annotated git tags, so
  `composer.json` must NOT contain a `version` field.

## Steps

1. **Update the changelog** — add a `## vX.Y - YYYY-MM-DD` section on top of
   [CHANGELOG.md](CHANGELOG.md) describing the release, and commit it.
2. **Prepare** — verifies the tree is clean, tests pass and the changelog
   documents the version; then sets `Version::VERSION` to the release
   version, commits and tags `vX.Y`, and bumps to the next `-SNAPSHOT`:
   ```bash
   ./scripts/release.sh prepare 0.9            # next dev version: 0.10-SNAPSHOT
   ./scripts/release.sh prepare 0.9 1.0-SNAPSHOT  # or choose it explicitly
   ```
3. **Perform** — pushes the branch and the release tag; Packagist picks the
   tag up automatically (or via the "Update" button on the package page):
   ```bash
   ./scripts/release.sh perform
   ```
   Verify the new version appears at
   https://packagist.org/packages/tttran/viet_qr_generator.

`./scripts/release.sh 0.9` is a shorthand that runs prepare then perform.

## Versioning

- `vX.Y` tags, following the existing scheme (v0.1 ... v0.8).
- Bump `Y` for features and fixes; bump `X` for breaking API changes.
- Between releases, `Version::VERSION` stays at the next planned version
  with a `-SNAPSHOT` suffix (Maven convention).
