import { execFileSync } from 'node:child_process';
import { envValueOr } from './env';

const HOST = envValueOr('E2E_DB_HOST', '127.0.0.1');
const PORT = envValueOr('E2E_DB_PORT', '9002');
const USER = envValueOr('E2E_DB_USER', 'root');
const PASSWORD = envValueOr('E2E_DB_PASSWORD', 'prestashop');

function mysqlArgs(sql: string, extra: string[] = []): string[] {
  return [
    '-h', HOST,
    '-P', PORT,
    '--protocol=tcp',
    `-u${USER}`,
    ...extra,
    'prestashop',
    '-e', sql,
  ];
}

/**
 * The password travels via MYSQL_PWD rather than a `-p<password>` argument,
 * which any user on the same machine could read out of the process list.
 */
function mysqlEnv(): NodeJS.ProcessEnv {
  return { ...process.env, MYSQL_PWD: PASSWORD };
}

export function runSql(sql: string): void {
  execFileSync('mysql', mysqlArgs(sql), { env: mysqlEnv() });
}

/** Returns the first column of the first row, or null when nothing matched. */
export function querySingleValue(sql: string): string | null {
  return readSingleValue(sql, ['--batch', '--skip-column-names']);
}

/**
 * Same, but without `--batch`'s backslash escaping.
 *
 * PHP's `json_encode` escapes forward slashes, so a stored URL is `https:\/\/…`
 * in the column — and `--batch` then escapes those backslashes again on the way
 * out, leaving `https:\\/\\/…`, which `JSON.parse` faithfully turns into a URL
 * with literal backslashes in it. Use this for any column whose exact bytes
 * matter; it cannot be the default because `--raw` also stops escaping newlines,
 * which would break the first-row/first-column parsing below.
 */
export function querySingleValueRaw(sql: string): string | null {
  return readSingleValue(sql, ['--batch', '--raw', '--skip-column-names']);
}

/** Every row's first column, in the order the query returned them. */
export function queryColumn(sql: string): string[] {
  const out = execFileSync('mysql', mysqlArgs(sql, ['--batch', '--skip-column-names']), {
    encoding: 'utf8',
    stdio: ['ignore', 'pipe', 'ignore'],
    env: mysqlEnv(),
  });
  return out
    .split('\n')
    .map((line) => line.trim())
    .filter((line) => line !== '' && line !== 'NULL');
}

function readSingleValue(sql: string, flags: string[]): string | null {
  const out = execFileSync('mysql', mysqlArgs(sql, flags), {
    encoding: 'utf8',
    stdio: ['ignore', 'pipe', 'ignore'],
    env: mysqlEnv(),
  });
  const value = out.split('\n')[0]?.trim();
  // `mysql --batch` prints the four characters NULL for a SQL NULL.
  if (!value || value === 'NULL') return null;
  return value;
}
