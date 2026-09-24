import { getGlobalConfig } from './config';

/** The PrestaShop version the shop under test actually runs, e.g. "8.1.5". */
export function prestashopVersion(): string {
  return getGlobalConfig('PS_VERSION_DB') ?? '';
}

export function prestashopMajor(): number {
  return Number(prestashopVersion().split('.')[0] || 0);
}

/**
 * Whether the shop serves PrestaShop 8's product editor ("products-v2").
 *
 * The suite drives one product page, and the two versions do not share it: PS8
 * renders a product-type badge that opens a type modal and a Combinations tab
 * with a Vue attribute generator, while 1.7.8 keeps combinations in the
 * Quantities step behind a tokenfield ("Combine several attributes, e.g. Size:
 * all") and offers no product-type switch at all. Specs that drive the product
 * page therefore state which editor they need instead of failing on a selector
 * that was never going to exist.
 */
export function hasProductPageV2(): boolean {
  return prestashopMajor() >= 8;
}
