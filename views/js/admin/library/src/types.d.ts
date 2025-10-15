// Custom elements declarations
declare namespace JSX {
  interface IntrinsicElements {
    'prestashop-accounts': React.DetailedHTMLProps<React.HTMLAttributes<HTMLElement>, HTMLElement>
  }
}

// PrestaShop Accounts Vue Component
interface PrestaShopAccountsVue {
  init(): void
  isOnboardingCompleted(): boolean
  openModal(): void
}

// MBO CDC Dependencies Resolver
interface MboCdcDependencyResolver {
  render(config: any): void
}

// PrestaShop CloudSync type definitions
interface CloudSyncSharingConsent {
  init(selector: string): void
  on(event: string, callback: (data: any) => void): void
  isOnboardingCompleted(callback: (isCompleted: boolean) => void): void
  destroy(): void
}

interface PrestaShopGlobals {
  [key: string]: any
}

// Window interface extensions
declare global {
  interface Window {
    cloudSyncSharingConsent: CloudSyncSharingConsent
    prestashop?: PrestaShopGlobals
    // Add support for PrestaShop's Media::addJsDef variables
    mollieConfig?: {
      apiUrl?: string
      customerEmail?: string
      shopId?: string
      environment?: 'live' | 'test'
      [key: string]: any
    }
    // MBO dependencies resolver
    mboCdcDependencyResolver?: MboCdcDependencyResolver
    // PS Accounts
    psaccountsVue?: PrestaShopAccountsVue
  }
}