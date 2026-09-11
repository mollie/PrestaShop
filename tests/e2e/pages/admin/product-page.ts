import type { Locator, Page } from '@playwright/test';

/**
 * PrestaShop 8's product page ("products-v2"), only as far as the subscription
 * coverage needs it: a product has to be a *combinations* product before the
 * Combinations tab exists at all, and the module's subscription options are
 * offered there as an ordinary attribute group.
 *
 * Deliberately driven through the UI rather than seeded with SQL: what is under
 * test is that the module's attribute group reaches the merchant-facing
 * generator, which a hand-written `ps_product_attribute` row would assert
 * nothing about.
 */
export class AdminProductPage {
  constructor(private page: Page) {}

  async goto(productId: number) {
    await this.page.goto(`/admin1/index.php?controller=AdminProducts&id_product=${productId}&updateproduct`);
    // The page redirects to its Symfony route; the type badge is the first
    // thing that proves the editor itself (not a login or a 500) rendered.
    await this.page.locator('.product-type-preview').waitFor({ timeout: 30_000 });
  }

  typeBadge(): Locator {
    return this.page.locator('.product-type-preview');
  }

  combinationsTabLink(): Locator {
    return this.page.locator('#form-nav a[href="#product_combinations-tab"]');
  }

  /**
   * Switches the product to "Product with combinations". Two dialogs, not one:
   * picking the type in the first opens a second that warns the change saves
   * the product immediately and resets its stock.
   */
  async switchToCombinationsType() {
    await this.typeBadge().click();

    const typeModal = this.page.locator('.modal-content').filter({ hasText: /change the product type/i }).first();
    await typeModal.waitFor({ timeout: 15_000 });
    await typeModal.locator('[data-value="combinations"]').click();
    await typeModal.getByRole('button', { name: /change product type/i }).first().click();

    const confirmModal = this.page.locator('.modal-content').filter({ hasText: /are you sure/i }).first();
    await confirmModal.waitFor({ timeout: 15_000 });
    await confirmModal.getByRole('button', { name: /change product type/i }).first().click();

    // The save-and-reload is asynchronous; the tab appearing is the signal that
    // the product came back as a combinations product.
    await this.combinationsTabLink().waitFor({ timeout: 60_000 });
  }

  async openCombinationsTab(): Promise<Locator> {
    await this.combinationsTabLink().click();
    const tab = this.page.locator('#product_combinations-tab');
    await tab.waitFor({ timeout: 20_000 });
    return tab;
  }

  /** The attribute-picking modal behind the tab's "Generate combinations" button. */
  async openCombinationsGenerator(): Promise<Locator> {
    const tab = this.page.locator('#product_combinations-tab');
    await tab.getByRole('button', { name: /generate combinations/i }).first().click();
    const modal = this.page.locator('.modal-content').filter({ hasText: /generate combinations/i }).last();
    await modal.waitFor({ timeout: 20_000 });
    return modal;
  }

  /** The attribute-group headings the generator offers, e.g. "Mollie Subscription". */
  async generatorAttributeGroups(modal: Locator): Promise<string[]> {
    return modal.locator('.attribute-group .attribute-group-name label').allInnerTexts();
  }

  /** Expands one group so its individual values become clickable. */
  async expandAttributeGroup(modal: Locator, groupId: number) {
    const header = modal.locator(`a[href="#attribute-group-${groupId}"]`);
    await header.click();
    await modal.locator(`#attribute-group-${groupId}`).waitFor({ state: 'visible', timeout: 15_000 });
  }

  /**
   * Selects attribute values. The checkbox itself is visually hidden, so the
   * click has to land on its label — the same thing a merchant clicks.
   */
  async selectAttributes(modal: Locator, attributeIds: number[]) {
    for (const id of attributeIds) {
      const label = modal.locator(`label[for="attribute_${id}"]`);
      await label.waitFor({ state: 'visible', timeout: 15_000 });
      await label.click();
    }
  }

  /**
   * Confirms the generator. Its button counts the pending combinations
   * ("Generate 2 combinations"), so it cannot be matched on a fixed label.
   */
  async confirmGeneration(modal: Locator) {
    await modal.locator('.btn-primary').filter({ hasText: /combinations/i }).last().click();
    await modal.waitFor({ state: 'hidden', timeout: 60_000 }).catch(() => {});
  }

  /** Combination rows whose attribute summary mentions the given text. */
  combinationRows(text: string | RegExp): Locator {
    return this.page.locator('#product_combinations-tab tr').filter({ hasText: text });
  }
}
