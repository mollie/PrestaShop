import { runSql } from './db';

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

export function setMethodConfig(
  methodId: string,
  cfg: { enabled?: boolean; api?: 'orders' | 'payments' }
): void {
  if (cfg.enabled !== undefined) {
    upsertConfig(`MOLLIE_METHOD_ENABLED_${methodId}`, cfg.enabled ? '1' : '0');
  }
  if (cfg.api !== undefined) {
    upsertConfig(`MOLLIE_METHOD_API_${methodId}`, cfg.api === 'orders' ? 'orders' : 'payments');
  }
}
