/**
 * Service to get configuration values injected by PHP controllers
 */
export class PrestaShopConfigService {
  /**
   * Get the PrestaShop Accounts CDN URL from PHP-injected Smarty variable
   */
  static getAccountsCdnUrl(): string | null {
    const w = window as any
    return w.urlAccountsCdn || null
  }

  /**
   * Get the contextPsAccounts injected by PHP
   */
  static getAccountsContext(): any {
    const w = window as any
    return w.contextPsAccounts || {}
  }

  /**
   * Get CloudSync CDC URL from PHP-injected variables
   */
  static getCloudSyncUrl(): string | null {
    const w = window as any
    return w.cloudSyncUrl || null
  }
}