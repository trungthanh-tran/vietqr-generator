#!/usr/bin/env bash
# Maven-like release script: prepare (verify) then perform (tag + push).
# Usage: ./scripts/release.sh <version>   e.g. ./scripts/release.sh 0.8
set -euo pipefail

if [ $# -ne 1 ]; then
    echo "Usage: $0 <version>  (e.g. $0 0.8)" >&2
    exit 1
fi

VERSION="$1"
TAG="v${VERSION}"
cd "$(dirname "$0")/.."

# Prepare: the working tree must be clean.
if [ -n "$(git status --porcelain)" ]; then
    echo "ERROR: working tree is not clean. Commit or stash your changes first." >&2
    exit 1
fi

# The tag must not already exist.
if git rev-parse -q --verify "refs/tags/${TAG}" >/dev/null; then
    echo "ERROR: tag ${TAG} already exists." >&2
    exit 1
fi

# The changelog must document this version.
if ! grep -q "^## ${TAG} " CHANGELOG.md; then
    echo "ERROR: CHANGELOG.md has no '## ${TAG} - YYYY-MM-DD' section. Add it first." >&2
    exit 1
fi

# Run the test suite when dependencies are installed.
if [ -x vendor/bin/phpunit ] || [ -f vendor/bin/phpunit ]; then
    php vendor/bin/phpunit tests
else
    echo "WARNING: vendor/bin/phpunit not found, skipping tests (run 'composer install' to enable)." >&2
fi

# Perform: create the annotated tag and push it.
git tag -a "${TAG}" -m "Release ${TAG}"
git push origin HEAD "${TAG}"

echo "Released ${TAG}. Packagist will pick the tag up automatically."
