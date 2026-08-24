#!/usr/bin/env bash
# Maven-like release flow (release:prepare / release:perform) for this
# Composer package. The version lives in src/Version.php (the pom.xml
# equivalent) and carries a -SNAPSHOT suffix during development; Packagist
# versions come from the annotated git tags this script creates.
#
# Usage:
#   scripts/release.sh prepare <version> [next-dev-version]
#       Verify the tree is clean, tests pass and CHANGELOG.md documents
#       <version>; set Version::VERSION to <version>, commit and tag
#       v<version>; then bump to <next-dev-version> (default: next minor
#       + "-SNAPSHOT") and commit the development iteration.
#   scripts/release.sh perform
#       Push the branch and the latest release tag to origin. Packagist
#       picks the tag up automatically (this is the "deploy" step).
#   scripts/release.sh <version>
#       Shorthand for prepare + perform.
set -euo pipefail

cd "$(dirname "$0")/.."
VERSION_FILE="src/Version.php"

set_version() {
    sed -i "s/const VERSION = '[^']*';/const VERSION = '$1';/" "$VERSION_FILE"
    if ! grep -q "const VERSION = '$1';" "$VERSION_FILE"; then
        echo "ERROR: failed to set version '$1' in ${VERSION_FILE}." >&2
        exit 1
    fi
}

next_dev_version() {
    # Maven default: bump the least significant segment.
    # 0.9 -> 0.10-SNAPSHOT, 1.2.3 -> 1.2.4-SNAPSHOT
    local prefix="${1%.*}" last="${1##*.}"
    echo "${prefix}.$((last + 1))-SNAPSHOT"
}

prepare() {
    local version="$1"
    local next="${2:-$(next_dev_version "$version")}"
    local tag="v${version}"

    if ! [[ "$version" =~ ^[0-9]+(\.[0-9]+)+$ ]]; then
        echo "ERROR: version must look like X.Y or X.Y.Z (e.g. 0.9), got '${version}'." >&2
        exit 1
    fi
    if [ -n "$(git status --porcelain)" ]; then
        echo "ERROR: working tree is not clean. Commit or stash your changes first." >&2
        exit 1
    fi
    if git rev-parse -q --verify "refs/tags/${tag}" >/dev/null; then
        echo "ERROR: tag ${tag} already exists." >&2
        exit 1
    fi
    if ! grep -q "^## ${tag} " CHANGELOG.md; then
        echo "ERROR: CHANGELOG.md has no '## ${tag} - YYYY-MM-DD' section. Add it first." >&2
        exit 1
    fi
    if [ -f vendor/bin/phpunit ]; then
        php vendor/bin/phpunit tests
    else
        echo "WARNING: vendor/bin/phpunit not found, skipping tests (run 'composer install' to enable)." >&2
    fi

    # Release commit and tag.
    set_version "$version"
    git add "$VERSION_FILE"
    git commit -m "Release ${tag}"
    git tag -a "${tag}" -m "Release ${tag}"

    # Next development iteration.
    set_version "$next"
    git add "$VERSION_FILE"
    git commit -m "Prepare next development iteration ${next}"

    echo "Prepared ${tag} (next development version: ${next})."
    echo "Run 'scripts/release.sh perform' to push it."
}

perform() {
    local tag
    tag="$(git describe --tags --abbrev=0)"
    git push origin HEAD "${tag}"
    echo "Released ${tag}. Packagist will pick the tag up automatically."
}

case "${1:-}" in
    prepare)
        [ $# -ge 2 ] || { echo "Usage: $0 prepare <version> [next-dev-version]" >&2; exit 1; }
        prepare "$2" "${3:-}"
        ;;
    perform)
        perform
        ;;
    "")
        echo "Usage: $0 prepare <version> [next-dev-version] | perform | <version>" >&2
        exit 1
        ;;
    *)
        prepare "$1"
        perform
        ;;
esac
