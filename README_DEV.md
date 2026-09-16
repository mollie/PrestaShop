# Mollie readme for developers

## Module production build

A production build of the module is uploaded as a workflow artifact every time a pull request is closed.

## PHP-CS-FIXER

The `php-cs-fixer` job runs in CI on every pull request. There is no local git hook, so nothing is
reformatted for you on commit. To fix the coding style before pushing, run:
`make fix-lint`

## React admin library

The back office React apps live in `views/js/admin/library`. Rebuild the compiled bundles with:
`make build-react`
