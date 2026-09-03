#!/bin/bash
# Usage: upgrading-module-test.sh <module> <ps-version> [base-tag]
#
# Installs the oldest supported release of the module into a shop already built by
# `make VERSION=<ps-version> e2eh<ps-version>`, checks the tree back out to the commit
# under test, runs that commit's own upgrade/*.php files, and asserts the shop's
# recorded version at both ends. The tree round trip happens in place because the
# container bind mounts the checkout as modules/<module>.

set -euo pipefail

# bash reads a script file in chunks as it executes, and the tag checkout below
# replaces this very file with a tree that does not contain it. Re-exec from a copy
# held in memory.
if [ -n "${BASH_SOURCE[0]:-}" ]; then
    source_text="$(cat "${BASH_SOURCE[0]}")"
    exec bash -c "$source_text" "$0" "$@"
fi

MODULE="${1:-mollie}"
PS_VERSION="${2:-1785}"
# Oldest release in the supported window; bump when Mollie 6 leaves support.
BASE_TAG="${3:-v6.0.1}"

PS_CONTAINER="prestashop-${MODULE}-${PS_VERSION}"
DB_CONTAINER="mysql-${MODULE}-${PS_VERSION}"

cd "$(git rev-parse --show-toplevel)"

# Read while the commit under test is still checked out. A branch name when there is
# one, so a local run ends where it started; CI only has the sha.
HEAD_REF="$(git symbolic-ref --quiet --short HEAD || git rev-parse HEAD)"
HEAD_VERSION="$(awk -F"'" '/this->version = /{print $2; exit}' "${MODULE}.php")"
BASE_VERSION="${BASE_TAG#v}"
# composer.lock is gitignored on the branch and tracked in the old tags, so the round
# trip would delete the one CI resolved for the head. Park it outside the checkout.
LOCK_BACKUP="$(cd .. && pwd)/composer.lock.${MODULE}-upgrading-module-test"

step() {
    echo
    echo "==> $*"
}

check_preconditions() {
    local missing=""

    for container in "$PS_CONTAINER" "$DB_CONTAINER"; do
        if [ -z "$(docker ps --quiet --filter "name=^${container}$")" ]; then
            missing="${missing} ${container}"
        fi
    done

    if [ -n "$missing" ]; then
        echo "Not running:${missing}" >&2
        echo "This target upgrades a shop that already exists; build one with" >&2
        echo "  make VERSION=${PS_VERSION} e2eh${PS_VERSION}" >&2
        return 1
    fi

    if [ ! -f composer.lock ]; then
        echo "No composer.lock to preserve across the tag checkout; run 'composer install'." >&2
        return 1
    fi
}

# Installing writes root-owned files (mails/<iso>) into the bind-mounted checkout, so
# the next checkout dies on "unable to unlink old". Docker Desktop hides this locally.
loosen_module_dir() {
    docker exec -i "$PS_CONTAINER" sh -c "chmod -R 777 /var/www/html/modules/${MODULE}"
}

console() {
    docker exec -i "$PS_CONTAINER" sh -c "cd /var/www/html && php bin/console $*"
}

# PrestaShop reports success for a no-op install, so without asking the shop what it
# recorded neither leg of this test can fail.
assert_module_version() {
    local expected="$1" installed

    installed="$(docker exec -i "$DB_CONTAINER" \
        mysql -uroot -pprestashop -N -B \
        -e "SELECT version FROM ps_module WHERE name = '${MODULE}'" prestashop 2>/dev/null)"

    if [ "$installed" != "$expected" ]; then
        echo "FAIL: expected the shop to report module version ${expected}, got '${installed}'" >&2
        return 1
    fi

    echo "OK: shop reports module version ${installed}"
}

# Raised as each thing stops being in the state it was found in.
shop_touched=0
tree_checked_out=0

cleanup() {
    local status=$?

    trap - EXIT

    if [ "$status" -ne 0 ]; then
        echo
        echo "FAILED (exit ${status})." >&2

        if [ "$tree_checked_out" = 1 ]; then
            echo "Restoring the working tree to ${HEAD_REF}." >&2
            loosen_module_dir || true
            git checkout --force "$HEAD_REF" || true
            echo "vendor/ now holds ${BASE_TAG} dependencies - run 'composer install'." >&2
        fi

        if [ "$shop_touched" = 1 ]; then
            echo "The shop is left mid-upgrade; rebuild it before trusting another run." >&2
        fi
    fi

    if [ -f "$LOCK_BACKUP" ]; then
        mv -f "$LOCK_BACKUP" composer.lock
    fi

    exit "$status"
}

trap cleanup EXIT

check_preconditions
git fetch --tags --force

step "Uninstalling ${MODULE} ${HEAD_VERSION}"
# e2eh<ps-version> leaves the module installed and `module install` is then a no-op
# that still exits 0, so without this the upgrade leg has nothing to upgrade. Uninstall
# while the current code still matches the schema it created.
shop_touched=1
console "prestashop:module uninstall ${MODULE}"

step "Checking out ${BASE_TAG}"
cp composer.lock "$LOCK_BACKUP"
loosen_module_dir
# A real checkout, not `git checkout <tag> .`: the partial form never deletes files
# added after the tag, so shared/ and config/services.yml stayed behind and got
# compiled against an autoloader rebuilt from the tag's composer.json.
tree_checked_out=1
git checkout --detach --force "$BASE_TAG"
rm -f composer.lock

step "Resolving ${BASE_TAG} dependencies"
# The tag pins roave/security-advisories dev-latest, which now conflicts with every
# guzzlehttp/psr7 it allows; --no-dev is not enough, composer still solves require-dev.
composer remove --dev roave/security-advisories --no-update --no-interaction
# Composer 2.9 refuses to load any package covered by an advisory and a release this
# old is full of them. This tree only exists to be upgraded away inside a container.
php -r '$f = "composer.json";
        $j = json_decode(file_get_contents($f), true);
        $j["config"]["policy"]["advisories"]["block"] = false;
        file_put_contents($f, json_encode($j, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));'
composer update --no-dev

step "Installing ${MODULE} ${BASE_VERSION}"
console "prestashop:module install ${MODULE}"
assert_module_version "$BASE_VERSION"

step "Checking out the commit under test (${HEAD_REF})"
loosen_module_dir
# $HEAD_REF, not develop: the old target asserted against develop, never the commit.
git checkout --force "$HEAD_REF"
mv "$LOCK_BACKUP" composer.lock
composer install

step "Upgrading ${MODULE} ${BASE_VERSION} to ${HEAD_VERSION}"
# Not `bin/console prestashop:module upgrade`: setModuleOnDiskFromAddons() unpacks the
# published release over the bind mount and upgrades that, not the commit under test.
docker exec -i "$PS_CONTAINER" \
    sh -c "cd /var/www/html && php modules/${MODULE}/.docker/upgrade-module.php ${MODULE}"
assert_module_version "$HEAD_VERSION"
