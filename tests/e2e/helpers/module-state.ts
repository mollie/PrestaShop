import { test } from '@playwright/test';
import { hasApiKeyConfigured } from './config';

/**
 * Decides whether an empty Mollie screen is a missing fixture or a regression.
 *
 * Most of this suite's screens render nothing at all until the module has an
 * API key connected — the method list, the settings form, the payment options.
 * Skipping on empty was therefore correct, but it also swallowed the case that
 * matters: a shop that IS connected and still shows nothing. That reads as a
 * green run over a module that never loaded, which is exactly how a whole
 * checkout phase passed against a shop with `MOLLIE_API_KEY_TEST` NULL.
 *
 * So: skip only when the module genuinely has no key, and fail loudly when it
 * has one. `setup/connect.setup.ts` makes the first case rare and deliberate.
 */
export function skipIfDisconnected(isEmpty: boolean, what: string): void {
  if (!isEmpty) return;

  test.skip(
    !hasApiKeyConfigured(),
    `${what} — the module has no API key connected (see setup/connect.setup.ts)`
  );

  throw new Error(
    `${what}, but the module HAS an API key connected. ` +
      'This is a regression in the module, not a missing test fixture.'
  );
}
