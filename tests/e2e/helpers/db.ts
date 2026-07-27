import { execFileSync } from 'node:child_process';

const HOST = process.env.E2E_DB_HOST || '127.0.0.1';
const PORT = process.env.E2E_DB_PORT || '9002';

export function runSql(sql: string): void {
  execFileSync('mysql', [
    '-h', HOST,
    '-P', PORT,
    '--protocol=tcp',
    '-uroot',
    '-pprestashop',
    'prestashop',
    '-e', sql,
  ]);
}
