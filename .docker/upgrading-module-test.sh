#!/bin/bash
# Usage: upgrading-module-test.sh <module> <ps-version> [base-tag]
#
# Installs the oldest supported release of the module into an already running shop,
# checks the tree back out to the commit under test, runs that commit's own
# upgrade/*.php files, and asserts the shop's recorded version at both ends.
#
# Expects a shop built by `make VERSION=<ps-version> e2eh<ps-version>`, so run it
# after that target and not on its own.
#
# The tree round trip happens in place because the PrestaShop container bind mounts
# the checkout as modules/<module>; there is no second copy to point it at.

set -euo pipefail

# bash reads a script file in chunks as it executes, and the tag checkout below
# replaces this very file with the tag's tree, which does not contain it. Re-exec
# from a copy held in memory so the rest of the run cannot be pulled out from
# under the interpreter.
if [ -n "${BASH_SOURCE[0]:-}" ]; then
    source_text="$(cat "${BASH_SOURCE[0]}")"
    exec bash -c "$source_text" "$0" "$@"
fi

MODULE="${1:-mollie}"
PS_VERSION="${2:-1785}"
# The oldest release in the supported window: Mollie's compatibility table pairs
# PS 1.7.6 - 9.0.0 with Mollie 6, and there is no v6.0.0 tag, only betas. Bump this
# when Mollie 6 leaves support.
BASE_TAG="${3:-v6.0.1}"

PS_CONTAINER="prestashop-${MODULE}-${PS_VERSION}"
DB_CONTAINER="mysql-${MODULE}-${PS_VERSION}"

cd "$(git rev-parse --show-toplevel)"

# Everything read from the tree has to be read now, while the commit under test is
# still checked out. A branch name when there is one, so a local run ends where it
# started; CI checks out a detached HEAD and only has the sha.
HEAD_REF="$(git symbolic-ref --quiet --short HEAD || git rev-parse HEAD)"
HEAD_VERSION="$(awk -F"'" '/this->version = /{print $2; exit}' "${MODULE}.php")"
BASE_VERSION="${BASE_TAG#v}"
# composer.lock is gitignored on the branch and tracked in the old tags, so the round
# trip would delete the one CI resolved for the head. Keep it outside the checkout,
# where no checkout can touch it.
LOCK_BACKUP="$(cd .. && pwd)/composer.lock.${MODULE}-upgrading-module-test"

step() {
    echo
    echo "==> $*"
}

# Everything below needs a shop already built by e2eh<ps-version> and the lock that
# built it. Checked up front because otherwise the target dies on a raw "No such
# container" or a bare cp error from whichever step ran first, neither of which says
# what is actually missing - and by then the module has already been uninstalled.
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

    # The head's lock is what gets set aside and restored around the tag checkout.
    if [ ! -f composer.lock ]; then
        echo "No composer.lock to preserve across the tag checkout; run 'composer install'." >&2
        return 1
    fi
}

# Installing the module inside the container writes into the bind-mounted checkout
# as root - PrestaShop generates a mails/<iso> folder per installed language - and
# the runner cannot replace those files afterwards, so the next checkout dies on
# "unable to unlink old 'mails/de/index.php': Permission denied". Docker Desktop
# remaps bind mounts to the host user and hides this locally.
loosen_module_dir() {
    docker exec -i "$PS_CONTAINER" sh -c "chmod -R 777 /var/www/html/modules/${MODULE}"
}

console() {
    docker exec -i "$PS_CONTAINER" sh -c "cd /var/www/html && php bin/console $*"
}

# PrestaShop reports success for a no-op install, and an upgrade that ran nothing at
# all also leaves a shop that boots, so without asking the shop what it recorded
# neither leg of this test can fail.
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

# Raised as soon as each is no longer in the state it was found in, so a failure
# reports only what it actually disturbed. Without them a run that fails before
# touching anything - no shop up, say - still tells the developer to reinstall
# dependencies and rebuild a shop.
shop_touched=0
tree_checked_out=0

# The steps below leave the checkout detached on $BASE_TAG with an edited
# composer.json and no composer.lock, so a failure halfway has to put the tree back
# or it takes the developer's working copy with it.
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
            # The tree is back, the rest of the run is not: vendor/ still holds the
            # base tag's dependencies, and no checkout can fix that.
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
# e2eh<ps-version> leaves the module installed at the version under test, and
# `module install` on an installed module is a no-op that still exits 0. Without this
# the shop never goes back to $BASE_TAG and the upgrade leg has nothing to upgrade -
# the job goes green having tested nothing. Uninstall while the current code is still
# checked out, so it matches the schema it created.
shop_touched=1
console "prestashop:module uninstall ${MODULE}"

step "Checking out ${BASE_TAG}"
cp composer.lock "$LOCK_BACKUP"
loosen_module_dir
# A real checkout of the tag, not `git checkout <tag> .`. The partial form restores
# the tag's files but never deletes files added after it, so everything the branch
# added since - shared/, config/services.yml - stayed behind and got compiled against
# an autoloader composer had just rebuilt from the tag's composer.json, which knows
# nothing about them.
tree_checked_out=1
git checkout --detach --force "$BASE_TAG"
rm -f composer.lock

step "Resolving ${BASE_TAG} dependencies"
# The tag's require-dev pins roave/security-advisories dev-latest, which always
# resolves to today's advisory list and by now conflicts with every guzzlehttp/psr7
# the tag's runtime constraints allow, so a release this old can no longer be
# resolved. --no-dev is not enough, composer still solves require-dev. Drop the
# package outright: it is an install-time guard and the shop only needs the runtime
# set. The checkout back to $HEAD_REF undoes the composer.json edit.
composer remove --dev roave/security-advisories --no-update --no-interaction
# Composer 2.9 refuses to load any package covered by a security advisory, and a
# release this old is full of them - symfony/http-client ^4.4 and guzzlehttp/psr7 1.x
# among its runtime requirements - so the resolve dies on the runner while it still
# succeeds on an older composer. Turn the policy off for the tag: this checkout exists
# only to be upgraded away from inside a throwaway container, and the head's own
# dependencies are resolved separately and still audited.
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
# $HEAD_REF, not develop: the old target ended on `git checkout develop --force`, so
# the upgrade leg asserted against develop and never saw the commit being tested.
git checkout --force "$HEAD_REF"
# The autoloader is still the tag's at this point.
mv "$LOCK_BACKUP" composer.lock
composer install

step "Upgrading ${MODULE} ${BASE_VERSION} to ${HEAD_VERSION}"
# Not `bin/console prestashop:module upgrade`: ModuleManager::upgrade() calls
# setModuleOnDiskFromAddons() first, which unpacks the published release over
# modules/<module> - a bind mount of this checkout - and upgrades that instead of the
# commit under test. .docker/upgrade-module.php runs the local upgrade/*.php files.
docker exec -i "$PS_CONTAINER" \
    sh -c "cd /var/www/html && php modules/${MODULE}/.docker/upgrade-module.php ${MODULE}"
assert_module_version "$HEAD_VERSION"
