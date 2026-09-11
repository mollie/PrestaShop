/**
 * Reads an environment variable, dropping a matching pair of surrounding
 * quotes.
 *
 * The Makefile exports the repo's `.env` with `include .env` + `export`, and
 * that file quotes its values (`SEGMENT_API_KEY=''`, `ENV_baseUrl8='http://…'`).
 * Make has no shell to strip those quotes, so a variable written as
 * `MOLLIE_TEST_API_KEY='test_…'` arrives here as `'test_…'` — quotes included.
 */
export function envValue(name: string): string | undefined {
  const raw = process.env[name];
  if (raw === undefined) return undefined;
  const unquoted = raw.replace(/^(['"])([\s\S]*)\1$/, '$2').trim();
  return unquoted === '' ? undefined : unquoted;
}

export function envValueOr(name: string, fallback: string): string {
  return envValue(name) ?? fallback;
}

/**
 * Whether the shop is reachable from the public internet.
 *
 * Mollie validates `webhookUrl` when creating a payment or order and rejects a
 * private host outright:
 *
 *   422 Unprocessable Entity — "The webhook URL is invalid because it is
 *   unreachable from Mollie's point of view." (field: webhookUrl)
 *
 * So no checkout can complete against localhost, however correct the test is.
 * The CI checkout job puts the shop behind a Cloudflare named tunnel for
 * exactly this reason.
 */
export function isPubliclyReachableBaseUrl(): boolean {
  const baseUrl = envValueOr('E2E_BASE_URL', 'http://localhost:8002');
  let host: string;
  try {
    host = new URL(baseUrl).hostname;
  } catch {
    return false;
  }
  return !(
    host === 'localhost' ||
    host === '127.0.0.1' ||
    host === '::1' ||
    host === '0.0.0.0' ||
    host.endsWith('.local') ||
    host.endsWith('.localhost')
  );
}
