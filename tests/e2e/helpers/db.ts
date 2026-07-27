import { execFileSync } from 'node:child_process';
import { envValueOr } from './env';

const HOST = envValueOr('E2E_DB_HOST', '127.0.0.1');
const PORT = envValueOr('E2E_DB_PORT', '9002');

function mysqlArgs(sql: string, extra: string[] = []): string[] {
  return [
    '-h', HOST,
    '-P', PORT,
    '--protocol=tcp',
    '-uroot',
    '-pprestashop',
    ...extra,
    'prestashop',
    '-e', sql,
  ];
}

export function runSql(sql: string): void {
  execFileSync('mysql', mysqlArgs(sql));
}

/** Returns the first column of the first row, or null when nothing matched. */
export function querySingleValue(sql: string): string | null {
  const out = execFileSync('mysql', mysqlArgs(sql, ['--batch', '--skip-column-names']), {
    encoding: 'utf8',
    stdio: ['ignore', 'pipe', 'ignore'],
  });
  const value = out.split('\n')[0]?.trim();
  // `mysql --batch` prints the four characters NULL for a SQL NULL.
  if (!value || value === 'NULL') return null;
  return value;
}
