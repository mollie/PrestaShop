// Declare global variable from PrestaShop
declare global {
  interface Window {
    mollieAdvancedSettingsAjaxUrl: string;
  }
}

export interface CarrierData {
  id: string;
  name: string;
  urlSource: string;
  customUrl: string;
}

export interface StatusMapping {
  mollieStatus: string;
  prestashopStatus: string;
  configKey: string;
}

export interface EmailStatus {
  status: string;
  enabled: boolean;
  configKey: string;
}

export interface OrderStatus {
  id: string;
  name: string;
}

export interface AdvancedSettingsData {
  // Order Settings
  invoiceOption: string;
  confirmationEmail: string;

  // Shipping Settings
  autoShip: boolean;
  autoShipStatuses: string[];
  carriers: CarrierData[];

  // Error Debugging
  debugMode: boolean;
  logLevel: string;

  // Visual Settings
  logoDisplay: string;
  cssPath: string;
  translateMollie: string;

  // Order Status Mapping
  statusMappings: StatusMapping[];

  // Order Status Emails
  emailStatuses: EmailStatus[];

  // Available options for dropdowns
  options: {
    orderStatuses: OrderStatus[];
    invoiceOptions: { id: string; name: string }[];
    confirmationEmailOptions: { id: string; name: string }[];
    logLevelOptions: { id: string; name: string }[];
    logoDisplayOptions: { id: string; name: string }[];
    translateMollieOptions: { id: string; name: string }[];
  };
}

export interface SaveCarrierData {
  id: string;
  urlSource: string;
  customUrl: string;
}

export interface SaveAdvancedSettingsData {
  invoiceOption?: string;
  confirmationEmail?: string;
  autoShip?: boolean;
  autoShipStatuses?: string[];
  carriers?: SaveCarrierData[];
  debugMode?: boolean;
  logLevel?: string;
  logoDisplay?: string;
  cssPath?: string;
  translateMollie?: string;
  statusMappings?: StatusMapping[];
  emailStatuses?: EmailStatus[];
}

export interface AdvancedSettingsResponse {
  success: boolean;
  message?: string;
  error?: string;
  data?: AdvancedSettingsData;
}

/**
 * Advanced Settings API Service
 * Handles AJAX calls to AdminMollieAdvancedSettings controller
 */
export class AdvancedSettingsApiService {
  private baseUrl: string;

  constructor() {
    // Use PrestaShop's generated URL with proper tokens
    this.baseUrl = window.mollieAdvancedSettingsAjaxUrl || '';
  }

  /**
   * Get all advanced settings
   */
  async getSettings(): Promise<AdvancedSettingsResponse> {
    const response = await fetch(`${this.baseUrl}&ajax=1&action=getSettings`, {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
      },
    });
    return response.json();
  }

  /**
   * Save all advanced settings
   */
  async saveSettings(data: SaveAdvancedSettingsData): Promise<AdvancedSettingsResponse> {
    const formData = new FormData();
    formData.append('ajax', '1');
    formData.append('action', 'saveSettings');
    formData.append('data', JSON.stringify(data));

    const response = await fetch(this.baseUrl, {
      method: 'POST',
      body: formData
    });
    return response.json();
  }
}

// Export singleton instance
export const advancedSettingsApiService = new AdvancedSettingsApiService();
