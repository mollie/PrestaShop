import { runSql, querySingleValue } from './db';

function quote(value: string): string {
  return value.replace(/\\/g, '\\\\').replace(/'/g, "\\'");
}

/**
 * `ps_configuration.name` only carries a NON-UNIQUE index, so
 * `INSERT ... ON DUPLICATE KEY UPDATE` never fires for it and would append a
 * duplicate row on every call. Delete-then-insert is idempotent and also
 * collapses any duplicate rows an earlier run may have left behind.
 */
function upsertConfig(name: string, value: string): void {
  const n = quote(name);
  const v = quote(value);
  runSql(
    `DELETE FROM ps_configuration WHERE name = '${n}'; ` +
    `INSERT INTO ps_configuration (name, value, date_add, date_upd) ` +
    `VALUES ('${n}', '${v}', NOW(), NOW())`
  );
}

export function setGlobalConfig(key: string, value: string): void {
  upsertConfig(key, value);
}

export function getGlobalConfig(key: string): string | null {
  return querySingleValue(`SELECT value FROM ps_configuration WHERE name = '${quote(key)}' LIMIT 1`);
}

/**
 * Whether the module has an API key stored at all. Every webhook call is
 * rejected with 401 before any other guard runs when it does not
 * (`controllers/front/webhook.php` checks `getApiClient()` first).
 */
export function hasApiKeyConfigured(): boolean {
  return Boolean(getGlobalConfig('MOLLIE_API_KEY_TEST') || getGlobalConfig('MOLLIE_API_KEY'));
}

/** Config::ENVIRONMENT_TEST / ENVIRONMENT_LIVE. */
export const ENVIRONMENT_TEST = 0;
export const DEFAULT_SHOP_ID = 1;

export function isTestEnvironment(): boolean {
  return (getGlobalConfig('MOLLIE_ENVIRONMENT') ?? String(ENVIRONMENT_TEST)) === String(ENVIRONMENT_TEST);
}

/**
 * Per-method state lives in `ps_mol_payment_method`, NOT in `ps_configuration`.
 *
 * The `MOLLIE_METHOD_ENABLED_<id>` / `MOLLIE_METHOD_API_<id>` names in
 * `Config.php` are *form field names*: `PaymentMethodService::savePaymentMethod`
 * reads them with `Tools::getValue()` and persists the result onto a
 * MolPaymentMethod row. Writing those names into `ps_configuration` therefore
 * has no effect at all — the module never reads them back.
 *
 * The module looks a method up by (id_method, live_environment, id_shop), so
 * that triple is the upsert key here too.
 */
export function setMethodConfig(
  methodId: string,
  cfg: { enabled?: boolean; api?: 'orders' | 'payments'; environment?: number; shopId?: number }
): void {
  const id = quote(methodId);
  const environment = cfg.environment ?? ENVIRONMENT_TEST;
  const shopId = cfg.shopId ?? DEFAULT_SHOP_ID;
  const where = `id_method = '${id}' AND live_environment = ${environment} AND id_shop = ${shopId}`;

  const existing = querySingleValue(
    `SELECT id_payment_method FROM ps_mol_payment_method WHERE ${where} LIMIT 1`
  );

  if (!existing) {
    const enabled = cfg.enabled === undefined ? 0 : cfg.enabled ? 1 : 0;
    const api = quote(cfg.api ?? 'orders');
    // Numeric columns are written as 0 rather than left NULL, matching what
    // PaymentMethodService::savePaymentMethod produces — the checkout-side
    // restriction validators compare against these.
    runSql(
      `INSERT INTO ps_mol_payment_method ` +
      `(id_method, method_name, enabled, method, description, is_countries_applicable, ` +
      ` minimal_order_value, max_order_value, surcharge, surcharge_fixed_amount_tax_excl, ` +
      ` tax_rules_group_id, surcharge_percentage, surcharge_limit, min_amount, max_amount, ` +
      ` images_json, live_environment, position, id_shop, is_manual_capture) ` +
      `SELECT '${id}', '${id}', ${enabled}, '${api}', '', 0, ` +
      ` 0, 0, 0, 0, 0, 0, 0, 0, 0, ` +
      ` '[]', ${environment}, COALESCE(MAX(position), 0) + 1, ${shopId}, 0 ` +
      `FROM ps_mol_payment_method WHERE live_environment = ${environment} AND id_shop = ${shopId}`
    );
    return;
  }

  const sets: string[] = [];
  if (cfg.enabled !== undefined) sets.push(`enabled = ${cfg.enabled ? 1 : 0}`);
  if (cfg.api !== undefined) sets.push(`method = '${quote(cfg.api)}'`);
  if (sets.length === 0) return;

  runSql(`UPDATE ps_mol_payment_method SET ${sets.join(', ')} WHERE ${where}`);
}

/** Removes every method row for an environment, so a phase starts from a known state. */
export function clearMethodConfig(environment = ENVIRONMENT_TEST, shopId = DEFAULT_SHOP_ID): void {
  runSql(
    `DELETE FROM ps_mol_payment_method WHERE live_environment = ${environment} AND id_shop = ${shopId}`
  );
}
