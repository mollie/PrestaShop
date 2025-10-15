/**
 * Utility for safely accessing PHP variables injected via PrestaShop's Media::addJsDef()
 * This provides type-safe access to PHP data in TypeScript components
 */

/**
 * Default configuration values that can be overridden by PHP
 */
interface DefaultConfig {
  apiUrl: string
  environment: 'live' | 'test'
  customerEmail: string
  shopId: string
  debug: boolean
}

/**
 * Configuration service for accessing PrestaShop injected variables
 */
export class PrestaShopConfigService {
  private static instance: PrestaShopConfigService
  private config: Partial<DefaultConfig> = {}

  private constructor() {
    this.loadConfig()
  }

  static getInstance(): PrestaShopConfigService {
    if (!PrestaShopConfigService.instance) {
      PrestaShopConfigService.instance = new PrestaShopConfigService()
    }
    return PrestaShopConfigService.instance
  }

  /**
   * Load configuration from window variables set by PHP
   */
  private loadConfig(): void {
    try {
      // Try to get Mollie-specific config first
      if (typeof window !== 'undefined' && (window as any).mollieConfig) {
        this.config = { ...this.config, ...(window as any).mollieConfig }
      }

      // Fallback to general prestashop config
      if (typeof window !== 'undefined' && (window as any).prestashop) {
        const psConfig = (window as any).prestashop
        
        // Map common PrestaShop variables
        if (psConfig.urls?.current_url) {
          this.config.apiUrl = psConfig.urls.current_url
        }
        
        if (psConfig.customer?.email) {
          this.config.customerEmail = psConfig.customer.email
        }
        
        if (psConfig.shop?.id) {
          this.config.shopId = psConfig.shop.id
        }

        if (psConfig.debug !== undefined) {
          this.config.debug = Boolean(psConfig.debug)
        }
      }

      // Load from any other global variables
      this.loadFromGlobals()
      
    } catch (error) {
      console.warn('Failed to load PrestaShop configuration:', error)
    }
  }

  /**
   * Load configuration from other global variables
   */
  private loadFromGlobals(): void {
    try {
      const w = window as any

      // Check for common PrestaShop admin variables
      if (w.admin_url) {
        this.config.apiUrl = w.admin_url
      }

      if (w.iso_user) {
        this.config.customerEmail = w.iso_user
      }

      if (w.id_shop) {
        this.config.shopId = w.id_shop
      }

      // Check for specific module variables
      if (w.mollie_environment) {
        this.config.environment = w.mollie_environment === 'live' ? 'live' : 'test'
      }

      if (w.mollie_debug !== undefined) {
        this.config.debug = Boolean(w.mollie_debug)
      }

    } catch (error) {
      console.warn('Failed to load from global variables:', error)
    }
  }

  /**
   * Get a configuration value with optional default
   */
  get<K extends keyof DefaultConfig>(key: K, defaultValue?: DefaultConfig[K]): DefaultConfig[K] | undefined {
    const value = this.config[key]
    return value !== undefined ? value : defaultValue
  }

  /**
   * Get a configuration value as string
   */
  getString(key: keyof DefaultConfig, defaultValue = ''): string {
    const value = this.get(key)
    return typeof value === 'string' ? value : String(value || defaultValue)
  }

  /**
   * Get a configuration value as boolean
   */
  getBoolean(key: keyof DefaultConfig, defaultValue = false): boolean {
    const value = this.get(key)
    if (typeof value === 'boolean') return value
    if (typeof value === 'string') return value === 'true' || value === '1'
    if (typeof value === 'number') return value !== 0
    return defaultValue
  }

  /**
   * Get a configuration value as number
   */
  getNumber(key: keyof DefaultConfig, defaultValue = 0): number {
    const value = this.get(key)
    if (typeof value === 'number') return value
    if (typeof value === 'string') {
      const parsed = parseFloat(value)
      return isNaN(parsed) ? defaultValue : parsed
    }
    return defaultValue
  }

  /**
   * Get all configuration
   */
  getAll(): Partial<DefaultConfig> {
    return { ...this.config }
  }

  /**
   * Set a configuration value (useful for testing or runtime updates)
   */
  set<K extends keyof DefaultConfig>(key: K, value: DefaultConfig[K]): void {
    this.config[key] = value
  }

  /**
   * Check if a configuration key exists
   */
  has(key: keyof DefaultConfig): boolean {
    return key in this.config && this.config[key] !== undefined
  }

  /**
   * Reload configuration from window variables
   */
  reload(): void {
    this.config = {}
    this.loadConfig()
  }

  /**
   * Get API URL with proper formatting
   */
  getApiUrl(): string {
    let apiUrl = this.getString('apiUrl')
    
    if (!apiUrl) {
      // Fallback to current domain
      apiUrl = window.location.origin
    }

    // Ensure trailing slash is removed for consistency
    return apiUrl.replace(/\/$/, '')
  }

  /**
   * Get environment with fallback
   */
  getEnvironment(): 'live' | 'test' {
    const env = this.get('environment')
    return env === 'live' || env === 'test' ? env : 'test'
  }

  /**
   * Check if debug mode is enabled
   */
  isDebugMode(): boolean {
    return this.getBoolean('debug', false)
  }
}

// Export singleton instance
export const prestashopConfig = PrestaShopConfigService.getInstance()

/**
 * React hook for accessing PrestaShop configuration
 */
export const usePrestaShopConfig = () => {
  return {
    get: prestashopConfig.get.bind(prestashopConfig),
    getString: prestashopConfig.getString.bind(prestashopConfig),
    getBoolean: prestashopConfig.getBoolean.bind(prestashopConfig),
    getNumber: prestashopConfig.getNumber.bind(prestashopConfig),
    getAll: prestashopConfig.getAll.bind(prestashopConfig),
    has: prestashopConfig.has.bind(prestashopConfig),
    getApiUrl: prestashopConfig.getApiUrl.bind(prestashopConfig),
    getEnvironment: prestashopConfig.getEnvironment.bind(prestashopConfig),
    isDebugMode: prestashopConfig.isDebugMode.bind(prestashopConfig),
    reload: prestashopConfig.reload.bind(prestashopConfig)
  }
}

/**
 * Utility function to safely access nested window properties
 */
export const safeWindowAccess = (path: string, defaultValue: any = undefined): any => {
  try {
    const keys = path.split('.')
    let current = window as any
    
    for (const key of keys) {
      if (current && typeof current === 'object' && key in current) {
        current = current[key]
      } else {
        return defaultValue
      }
    }
    
    return current
  } catch (error) {
    console.warn(`Failed to access window.${path}:`, error)
    return defaultValue
  }
}