import { runSql, querySingleValue, querySingleValueRaw } from './db';

/** Config::ENVIRONMENT_TEST / ENVIRONMENT_LIVE. */
export const ENVIRONMENT_TEST = 0;
export const DEFAULT_SHOP_ID = 1;

function quote(value: string): string {
  return value.replace(/\\/g, '\\\\').replace(/'/g, "\\'");
}

/**
 * Method ids come from the registry today, so this never fires — it exists so
 * that a future caller passing page-derived text into the SQL below gets a
 * loud error instead of a quietly interpolated string.
 */
function assertMethodIdShape(methodId: string): void {
  if (!/^[a-z0-9_-]+$/i.test(methodId)) {
    throw new Error(`refusing to use unsafe method id in SQL: ${JSON.stringify(methodId)}`);
  }
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

/**
 * Reads a configuration value the way the module reads it.
 *
 * `ps_configuration` holds one row per shop context, and `Configuration::get`
 * resolves the shop-specific row first, then the shop group's, then the global
 * one — so a bare `LIMIT 1` can return a value the module never sees. The PS1785
 * seed is exactly that case: it ships shop-scoped `SUBSCRIPTION_ATTRIBUTE_GROUP`
 * rows pointing at an older attribute group, while a fresh module install writes
 * a global row pointing at the group it just created. Whichever row MySQL
 * happened to return first then decided whether a test looked at the group the
 * module uses or an orphaned one.
 */
export function getGlobalConfig(key: string, shopId = DEFAULT_SHOP_ID): string | null {
  return querySingleValue(
    `SELECT value FROM ps_configuration WHERE name = '${quote(key)}' ` +
    `ORDER BY CASE ` +
    `  WHEN id_shop = ${Number(shopId)} THEN 0 ` +
    `  WHEN id_shop IS NULL AND id_shop_group IS NOT NULL THEN 1 ` +
    `  WHEN id_shop IS NULL AND id_shop_group IS NULL THEN 2 ` +
    `  ELSE 3 END, id_configuration DESC LIMIT 1`
  );
}

export function deleteGlobalConfig(key: string): void {
  runSql(`DELETE FROM ps_configuration WHERE name = '${quote(key)}'`);
}

/**
 * Puts a configuration key back exactly as it was found, including the case
 * where it did not exist at all — `MOLLIE_SANDBOX_SINGLE_CLICK_PAYMENT` is
 * absent from a fresh install rather than set to 0, and writing a 0 in its
 * place would leave the shop in a state the module never produces.
 */
export function restoreGlobalConfig(key: string, previous: string | null): void {
  if (previous === null) {
    deleteGlobalConfig(key);
    return;
  }
  setGlobalConfig(key, previous);
}

/**
 * Whether the module has an API key stored at all. Every webhook call is
 * rejected with 401 before any other guard runs when it does not
 * (`controllers/front/webhook.php` checks `getApiClient()` first).
 */
export function hasApiKeyConfigured(): boolean {
  return Boolean(getGlobalConfig('MOLLIE_API_KEY_TEST') || getGlobalConfig('MOLLIE_API_KEY'));
}

export function isTestEnvironment(): boolean {
  return (getGlobalConfig('MOLLIE_ENVIRONMENT') ?? String(ENVIRONMENT_TEST)) === String(ENVIRONMENT_TEST);
}

/**
 * `Config::MOLLIE_SINGLE_CLICK_PAYMENT` is not one key but a pair keyed on the
 * environment (`src/Config/Config.php`), and the module reads the one that
 * matches `MOLLIE_ENVIRONMENT`. Writing the sandbox key on a live-mode shop
 * would set a value nothing reads.
 */
export function singleClickConfigKey(): string {
  return isTestEnvironment()
    ? 'MOLLIE_SANDBOX_SINGLE_CLICK_PAYMENT'
    : 'MOLLIE_PRODUCTION_SINGLE_CLICK_PAYMENT';
}

/** Config::MOLLIE_IMAGES and its three documented values. */
export const IMAGES_KEY = 'MOLLIE_IMAGES';
export const LOGOS_HIDE = 'hide';
export const LOGOS_NORMAL = 'normal';
export const LOGOS_BIG = 'big';

/** Subscription\Config::MOLLIE_SUBSCRIPTION_ENABLED — gates the FO account tab. */
export const SUBSCRIPTIONS_ENABLED_KEY = 'MOLLIE_SUBSCRIPTION_ENABLED';

/**
 * Whether the method row carries the image set the module copies off Mollie's
 * method list when it is saved. Rows written by the cfg-* setups carry `[]`,
 * because those bypass the form; a logo can only be asserted once the method
 * has been saved through `PaymentMethodService::savePaymentMethod`.
 */
export function methodImages(methodId: string, environment = ENVIRONMENT_TEST, shopId = DEFAULT_SHOP_ID): string | null {
  assertMethodIdShape(methodId);
  // Raw: the column holds JSON whose slashes PHP escaped, and `--batch` would
  // escape those escapes (see querySingleValueRaw).
  return querySingleValueRaw(
    `SELECT images_json FROM ps_mol_payment_method ` +
    `WHERE id_method = '${quote(methodId)}' AND live_environment = ${environment} AND id_shop = ${shopId} LIMIT 1`
  );
}

/** Whether the module considers the method enabled, read straight off its row. */
export function isMethodEnabled(methodId: string, environment = ENVIRONMENT_TEST, shopId = DEFAULT_SHOP_ID): boolean {
  assertMethodIdShape(methodId);
  return (
    querySingleValue(
      `SELECT enabled FROM ps_mol_payment_method ` +
      `WHERE id_method = '${quote(methodId)}' AND live_environment = ${environment} AND id_shop = ${shopId} LIMIT 1`
    ) === '1'
  );
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
  assertMethodIdShape(methodId);
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
