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

// Extend global Window interface
declare global {
  interface Window {
    mollieAuthAjaxUrl: string;
    mollieAuthTranslations: MollieAuthTranslations;
  }
}