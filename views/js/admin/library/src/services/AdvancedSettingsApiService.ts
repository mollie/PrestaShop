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
  invoiceOption: string;
  confirmationEmail: string;
  autoShip: boolean;
  autoShipStatuses: string[];
  carriers: CarrierData[];
  logoDisplay: string;
  cssPath: string;
  translateMollie: string;
  statusMappings: StatusMapping[];
  emailStatuses: EmailStatus[];
  options: {
    orderStatuses: OrderStatus[];
    invoiceOptions: { id: string; name: string }[];
    confirmationEmailOptions: { id: string; name: string }[];
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
  not_configured?: boolean;
  data?: AdvancedSettingsData;
}

export class AdvancedSettingsApiService {
  private baseUrl: string;

  constructor() {
    // Force HTTPS if current page is HTTPS (fixes mixed content issues with proxies/ngrok)
    let url = window.mollieAdvancedSettingsAjaxUrl || '';
    if (window.location.protocol === 'https:' && url.startsWith('http:')) {
      url = url.replace('http:', 'https:');
    }
    this.baseUrl = url;
  }

  async getSettings(): Promise<AdvancedSettingsResponse> {
    const url = new URL(this.baseUrl, window.location.origin);
    url.searchParams.set('ajax', '1');
    url.searchParams.set('action', 'getSettings');

    const response = await fetch(url.toString(), {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
      },
    });
    return response.json();
  }

  async saveSettings(data: SaveAdvancedSettingsData): Promise<AdvancedSettingsResponse> {
    const url = new URL(this.baseUrl, window.location.origin);

    const formData = new FormData();
    formData.append('ajax', '1');
    formData.append('action', 'saveSettings');
    formData.append('data', JSON.stringify(data));

    const response = await fetch(url.toString(), {
      method: 'POST',
      body: formData
    });
    return response.json();
  }
}

export const advancedSettingsApiService = new AdvancedSettingsApiService();
