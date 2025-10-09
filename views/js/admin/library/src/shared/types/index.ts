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
  switchEnvironment: string;
  confirmSwitchEnvironment: string;
  cancel: string;
  switchTo: string;
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
  selectedCountries: string;
  specificCountries: string;
  acceptPaymentsFromSpecificCountries: string;
  selectCountriesAccept: string;
  excludePaymentsFromCountries: string;
  selectCountriesToExclude: string;
  excludeCustomerGroups: string;
  selectCustomerGroups: string;
  customerGroupsHelp: string;
  guest: string;
  customerGroup: string;
  
  // Payment Fees
  paymentFees: string;
  enablePaymentFee: string;
  paymentFeeType: string;
  fixedFee: string;
  fixedFeeTaxIncl: string;
  fixedFeeTaxExcl: string;
  percentageFee: string;
  percentageFeeLabel: string;
  combinedFee: string;
  noFee: string;
  paymentFeeTaxGroup: string;
  taxRulesGroupForFixedFee: string;
  maximumFee: string;
  minimumAmount: string;
  maximumAmount: string;
  minOrderAmount: string;
  maxOrderAmount: string;
  paymentFeeEmailHelp: string;

  // Order Restrictions
  orderRestrictions: string;
  
  // Actions
  save: string;
  saving: string;
  loadingMethods: string;
  loadingError: string;
  saveSettings: string;

  // Transaction Description Help
  transactionDescriptionHelp: string;
  transactionDescriptionVariables: string;

  // Messages
  paymentMethodNotFound: string;
  settingsSavedSuccessfully: string;
  failedToSaveSettings: string;
  paymentMethodsOrderUpdated: string;
  failedToUpdateOrder: string;
  savingNewOrder: string;
  noPaymentMethods: string;
  paymentMethodsWillAppear: string;

  // Custom Logo Upload
  pleaseUploadJpgOrPng: string;
  fileSizeTooLarge: string;
  imageDimensionsTooLarge: string;
  failedToUploadLogo: string;
  invalidImageFile: string;
  uploading: string;
  customLogoPreview: string;
  logoUploadedSuccessfully: string;
  customLogo: string;
  remove: string;

  // Apple Pay Button Descriptions
  applePayButtonBlackDesc: string;
  applePayButtonOutlineDesc: string;
  applePayButtonWhiteDesc: string;

  // Select Placeholders
  selectOption: string;
  selectOptions: string;
  itemsSelected: string;

  // Drag and drop
  dragPaymentOptionsToReorder: string;

  // Voucher Category
  voucherCategory: string;
  voucherCategoryNone: string;
  voucherCategoryMeal: string;
  voucherCategoryGift: string;
  voucherCategoryEco: string;
  voucherCategoryAll: string;
  voucherCategoryHelp: string;
}

export interface MollieAdvancedSettingsTranslations {
  advancedSettings: string;
  orderSettings: string;
  shippingSettings: string;
  errorDebugging: string;
  visualSettings: string;
  orderStatusMapping: string;
  orderStatusEmails: string;
  invoiceOption: string;
  confirmationEmail: string;
  autoShip: string;
  debugMode: string;
  logLevel: string;
  logoDisplay: string;
  translateMollie: string;
  cssPath: string;
  saveSuccess: string;
  saveError: string;
}

// Extend global Window interface
declare global {
  interface Window {
    mollieAuthAjaxUrl: string;
    mollieAuthTranslations: MollieAuthTranslations;
    molliePaymentMethodsAjaxUrl: string;
    molliePaymentMethodsTranslations: MolliePaymentMethodsTranslations;
    mollieAdvancedSettingsAjaxUrl: string;
    mollieAdvancedSettingsTranslations: MollieAdvancedSettingsTranslations;
  }
}