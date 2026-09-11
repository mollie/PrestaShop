import { envValue } from './env';

/** Mollie keys are `test_`/`live_` followed by at least 30 word characters. */
export const MOLLIE_KEY_SHAPE = /^(test|live)_\w{30,}$/;

/**
 * `MOLLIE_TEST_API_KEY` from the environment, or undefined when unset or not
 * shaped like a Mollie key. Shape-checked, not just truthy: a shell or .env
 * quoting slip otherwise reaches the tests as a non-empty string and fails as
 * if the module were broken. Shared by `setup/connect.setup.ts` and
 * `specs/admin/authorization.spec.ts` so the check cannot drift between them.
 */
export function envTestApiKey(): string | undefined {
  const key = envValue('MOLLIE_TEST_API_KEY');
  return key && MOLLIE_KEY_SHAPE.test(key) ? key : undefined;
}
