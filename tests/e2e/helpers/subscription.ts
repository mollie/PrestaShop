import { querySingleValue } from './db';
import { getGlobalConfig } from './config';

/**
 * The subscription attribute group and its values are created by the module at
 * install time (`subscription/Install/AttributeInstaller.php`), which records
 * every id it created in `ps_configuration`. Reading them back from there is
 * how a test addresses them without hardcoding ids that differ per shop — the
 * group is 5 on the PS8 seed only because of how many groups the seed already
 * ships.
 */
export const SUBSCRIPTION_ATTRIBUTE_KEYS = {
  group: 'SUBSCRIPTION_ATTRIBUTE_GROUP',
  none: 'SUBSCRIPTION_ATTRIBUTE_NONE',
  daily: 'SUBSCRIPTION_ATTRIBUTE_DAILY',
  weekly: 'SUBSCRIPTION_ATTRIBUTE_WEEKLY',
  monthly: 'SUBSCRIPTION_ATTRIBUTE_MONTHLY',
  quarterly: 'SUBSCRIPTION_ATTRIBUTE_QUARTERLY',
  yearly: 'SUBSCRIPTION_ATTRIBUTE_YEARLY',
} as const;

export type SubscriptionAttributeIds = {
  group: number | null;
  none: number | null;
  daily: number | null;
  weekly: number | null;
  monthly: number | null;
  quarterly: number | null;
  yearly: number | null;
};

export function subscriptionAttributeIds(): SubscriptionAttributeIds {
  const read = (key: string): number | null => {
    const raw = getGlobalConfig(key);
    const id = Number(raw);
    return Number.isInteger(id) && id > 0 ? id : null;
  };

  return {
    group: read(SUBSCRIPTION_ATTRIBUTE_KEYS.group),
    none: read(SUBSCRIPTION_ATTRIBUTE_KEYS.none),
    daily: read(SUBSCRIPTION_ATTRIBUTE_KEYS.daily),
    weekly: read(SUBSCRIPTION_ATTRIBUTE_KEYS.weekly),
    monthly: read(SUBSCRIPTION_ATTRIBUTE_KEYS.monthly),
    quarterly: read(SUBSCRIPTION_ATTRIBUTE_KEYS.quarterly),
    yearly: read(SUBSCRIPTION_ATTRIBUTE_KEYS.yearly),
  };
}

/** The attribute name the module gives each option, as it appears in the BO. */
export function subscriptionAttributeNames(): string[] {
  return ['None', 'Daily', 'Weekly', 'Monthly', 'Quarterly', 'Yearly'];
}

/** `ps_product.product_type` — 'standard' until the BO switches it. */
export function productType(productId: number): string | null {
  return querySingleValue(`SELECT product_type FROM ps_product WHERE id_product = ${Number(productId)} LIMIT 1`);
}

/**
 * How many of the product's combinations carry an attribute from the
 * subscription group. Lets the spec skip generating them a second time, so a
 * re-run against an already-prepared shop asserts rather than duplicates.
 */
export function subscriptionCombinationCount(productId: number, groupId: number): number {
  const count = querySingleValue(
    `SELECT COUNT(*) FROM ps_product_attribute pa ` +
    `JOIN ps_product_attribute_combination pac ON pac.id_product_attribute = pa.id_product_attribute ` +
    `JOIN ps_attribute a ON a.id_attribute = pac.id_attribute ` +
    `WHERE pa.id_product = ${Number(productId)} AND a.id_attribute_group = ${Number(groupId)}`
  );
  return Number(count ?? 0);
}
