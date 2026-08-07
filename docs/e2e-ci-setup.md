# E2E CI setup

What has to exist outside this repo before `.github/workflows/e2e-playwright.yml`
can run green. Nothing here is created by the workflow itself.

## 1. Repository secrets

| Secret | Used by | Notes |
|---|---|---|
| `MOLLIE_TEST_API_KEY` | both jobs | A `test_…` key from the Mollie test account. Must match `^(test\|live)_\w{30,}$` — the suite shape-checks it and skips rather than failing on a quoting slip. |
| `CF_TUNNEL_TOKEN_PS8_CHECKOUT` | `checkout` (PS8) | Connector token of the `ps8-checkout` named tunnel. |
| `CF_TUNNEL_TOKEN_PS1785_CHECKOUT` | `checkout` (PS1785) | Connector token of the `ps1785-checkout` named tunnel. |

Without the tunnel tokens the `checkout` job fails fast with an explicit error.
Without `MOLLIE_TEST_API_KEY` the connect step skips itself and every checkout
test then skips as "not offered at the payment step" — a green job that tested
nothing, so treat a fully-skipped checkout phase as a failure.

## 2. Repository variables (optional)

| Variable | Default |
|---|---|
| `MOLLIE_CI_HOSTNAME_PS8` | `ps8-checkout.invertusdemo.com` |
| `MOLLIE_CI_HOSTNAME_PS1785` | `ps1785-checkout.invertusdemo.com` |

Set these only to repoint a tunnel without editing the workflow.

## 3. The two Cloudflare named tunnels

One per matrix entry — two runners must never share a tunnel, or Cloudflare
splits requests between the two connectors. Created once, by hand:

```bash
cloudflared tunnel login

cloudflared tunnel create ps8-checkout
cloudflared tunnel route dns ps8-checkout ps8-checkout.invertusdemo.com

cloudflared tunnel create ps1785-checkout
cloudflared tunnel route dns ps1785-checkout ps1785-checkout.invertusdemo.com
```

Then, in the Zero Trust dashboard (Networks → Tunnels → the tunnel → Public
Hostname), map each hostname to the local shop:

```
Hostname: ps8-checkout.invertusdemo.com
Service:  HTTP://localhost:8002
```

`localhost:8002` is the port `docker-compose.8.yml` and `docker-compose.1785.yml`
both publish. The service must be **HTTP**, not HTTPS: the container has no TLS.
Cloudflare terminates TLS and forwards plain HTTP with `X-Forwarded-Proto:
https`, which is why the workflow runs `make trust-forwarded-proto` — without it
PrestaShop sees `http`, keeps redirecting to its own canonical `https` URL and
either loops or lands on `/security/compromised`.

Copy each tunnel's connector token from the dashboard's install command into the
matching GitHub secret.

## 4. Mollie test-account expectations

- Test mode enabled, with the methods in `tests/e2e/data/payment-methods.ts`
  active on the profile. Anything the profile does not offer is skipped per
  method, not failed.
- `voucher` and `giftcard` are `test.fixme()` — Mollie's test account
  auto-disables them.
- `creditcard` is `test.fixme()` — sandbox Mollie Components renders the card
  fields in inline iframes, a different flow from every other method.

## 5. Why `checkout` is serialised

The job carries `concurrency: e2e-tunnel-<matrix.prestashop>` with
`cancel-in-progress: false`. Two PRs running at once would attach a second
connector to the same tunnel, so runs queue per tunnel rather than cancelling
each other.

## Running the checkout phases locally

They cannot work against `localhost`: Mollie rejects an unreachable `webhookUrl`
with a 422, so the specs skip themselves (`isPubliclyReachableBaseUrl`). Use a
tunnel:

```bash
# .env: CF_TUNNEL_TOKEN=<connector token>
make VERSION=8 e2eh8_local
make tunnel                                       # leave running

make VERSION=8 set-shop-domain HOST=ps8-checkout.invertusdemo.com
make VERSION=8 trust-forwarded-proto

cd tests/e2e
export E2E_BASE_URL=https://ps8-checkout.invertusdemo.com
export MOLLIE_TEST_API_KEY=test_…
npx playwright test --project=admin authorization   # connect first
E2E_CHECKOUT_API=orders   npx playwright test --project=checkout-orders
E2E_CHECKOUT_API=payments npx playwright test --project=cfg-payments --project=checkout-payments
```

Point the tunnel back at whatever it was serving when you are done, and re-run
`make VERSION=8 set-shop-domain HOST=localhost:8002 SSL=0` to restore the shop.
