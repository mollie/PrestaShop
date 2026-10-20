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

Vite writes the scripts to `views/js/admin/library/dist/assets/` and the stylesheets to
`views/css/admin/library/`. PrestaShop expects stylesheets under `/views/css`, so a plugin in
`vite.config.ts` moves them there. Both directories are git ignored and built in CI.

## PrestaShop Addons validator

Findings below were reviewed against the 6.4.6 package and deliberately left alone. Check this list
before re-investigating a validator report.

### Escaping in templates

The validator flags every `nofilter` it sees, without reading the filter in front of it.

- `views/templates/front/subscription/customerRecurringOrderDetail.tpl` renders
  `{$recurringOrderData.order.addresses.*.formatted nofilter}`. The value comes from PrestaShop's own
  `OrderPresenter` and already contains `<br />` tags. Core renders the same expression the same way
  in `themes/classic/templates/customer/order-detail.tpl`. Escaping it would print the tags to
  customers.
- `views/templates/front/mollie_wait.tpl` renders
  `{$checkStatusEndpoint|escape:'javascript':'UTF-8' nofilter}`. The value is escaped for a
  JavaScript string. `nofilter` only stops Smarty adding a second, HTML-level escape that would
  corrupt the URL.

### Use of class Context

The validator reports 46 files. Three groups, none of them a defect:

- Six are not PrestaShop's `Context` at all. `$this->module->getService(Context::class)` resolves to
  `Mollie\Adapter\Context` through the `use` statement in each file. The validator does not resolve
  namespaces.
- Eight are `src/Adapter/*` and `src/Factory/ContextFactory.php`. These exist to keep
  `Context::getContext()` behind an interface, which is the pattern the guidance asks for. The
  validator found the wrapper and reported it as the leak.
- The rest are direct calls in controllers and services. There is no other way to reach the cart,
  customer, shop or language. Route new code through the adapters rather than rewriting these.

### PrestaShopBundle\Security\Annotation in PS9

`subscription/Controller/Symfony/SubscriptionController.php` and `SubscriptionFAQController.php` use
`@AdminSecurity` docblock annotations. This is a deprecation, not a break. In PrestaShop 9.2
`Security\Annotation\AdminSecurity` still ships as a deprecated subclass of the attribute, and
`AdminSecurityListener` reads attributes first and falls back to the annotation reader, so the
permission checks still apply.

Do not swap the import. PrestaShop 8.2 ships `Security\Annotation` only, with no `Security\Attribute`
namespace, so a straight change breaks PS8. Attributes also need PHP 8, and the module targets PHP
7.2.5. Move to attributes when the module drops PrestaShop 8 support, or handle both the way
`src/PsCompat/AdminBaseController.php` does.
