export interface MollieConfig {
  mode: 'live' | 'test'
  apiKey: string
  isConnected: boolean
}

export interface AuthorizationState {
  config: MollieConfig
  loading: boolean
  error: string | null
}

// PrestaShop global variables types
export interface MollieAuthTranslations {
  mode: string;
  modeDescription: string;
  live: string;
  test: string;
  testApiKey: string;
  liveApiKey: string;
  apiKeyPlaceholder: string;
  apiKeyDescription: string;
  connect: string;
  connecting: string;
  connected: string;
  connectedSuccessfully: string;
  show: string;
  hide: string;
  whereApiKey: string;
  needHelp: string;
  getStarted: string;
  mollieDocumentation: string;
  paymentsQuestions: string;
  contactMollieSupport: string;
  integrationQuestions: string;
  contactModuleDeveloper: string;
  newToMollie: string;
  createAccount: string;
  apiConfiguration: string;
  selectModeDescription: string;
  connectionFailed: string;
  failedToLoadSettings: string;
  failedToSwitchEnvironment: string;
}

export interface MolliePaymentMethodsTranslations {
  // Main page
  paymentMethods: string;
  configurePaymentMethods: string;
  enabled: string;
  disabled: string;
  enabledPaymentMethods: string;
  disabledPaymentMethods: string;
  
  // Payment method card
  showSettings: string;
  hideSettings: string;
  active: string;
  inactive: string;
  
  // Basic settings
  basicSettings: string;
  activateDeactivate: string;
  enablePaymentMethod: string;
  useEmbeddedCreditCardForm: string;
  enableMollieComponents: string;
  letCustomerSaveCreditCard: string;
  useOneClickPayments: string;
  paymentTitle: string;
  paymentTitlePlaceholder: string;
  
  // API Selection
  apiSelection: string;
  payments: string;
  orders: string;
  transactionDescription: string;
  transactionDescriptionPlaceholder: string;
  readMore: string;
  aboutDifferences: string;
  
  // Custom Logo
  useCustomLogo: string;
  uploadLogo: string;
  replaceLogo: string;
  logoUploadHelp: string;
  
  // Apple Pay Settings
  applePayDirectSettings: string;
  applePayDirectProductPage: string;
  enableApplePayProductPages: string;
  applePayDirectCartPage: string;
  enableApplePayCartPages: string;
  applePayDirectButtonStyle: string;
  applePayButtonBlack: string;
  applePayButtonOutline: string;
  applePayButtonWhite: string;
  
  // Payment Restrictions
  paymentRestrictions: string;
  acceptPaymentsFrom: string;
  allCountries: string;
  specificCountries: string;
  excludePaymentsFromCountries: string;
  selectCountriesToExclude: string;
  excludeCustomerGroups: string;
  selectCustomerGroups: string;
  guest: string;
  customerGroup: string;
  
  // Payment Fees
  paymentFees: string;
  
  // Order Restrictions
  orderRestrictions: string;
  
  // Actions
  save: string;
  saving: string;
  loadingMethods: string;
  loadingError: string;
  
  
  // Drag and drop
  dragPaymentOptionsToReorder: string;
}

// Extend global Window interface
declare global {
  interface Window {
    mollieAuthAjaxUrl: string;
    mollieAuthTranslations: MollieAuthTranslations;
    molliePaymentMethodsAjaxUrl: string;
    molliePaymentMethodsTranslations: MolliePaymentMethodsTranslations;
  }
}