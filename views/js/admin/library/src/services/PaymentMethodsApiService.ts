// Declare global variable from PrestaShop
declare global {
  interface Window {
    molliePaymentMethodsAjaxUrl: string;
  }
}

export interface PaymentMethod {
  id: string
  name: string
  type: "card" | "other"
  status: "active" | "inactive"
  isExpanded: boolean
  position: number
  image?: {
    size1x: string
    size2x: string
    svg: string
  }
  settings: {
    enabled: boolean
    title: string
    mollieComponents: boolean
    oneClickPayments: boolean
    transactionDescription: string
    apiSelection: "payments" | "orders"
    useCustomLogo: boolean
    customLogoUrl?: string | null
    paymentRestrictions: {
      acceptFrom: string
      excludeCountries: string[]
      excludeCustomerGroups: string[]
    }
    paymentFees: {
      enabled: boolean
      type: "fixed" | "percentage"
      taxGroup: string
      maxFee: string
      minAmount: string
      maxAmount: string
    }
    orderRestrictions: {
      minAmount: string
      maxAmount: string
    }
    applePaySettings?: {
      directProduct?: boolean
      directCart?: boolean
      buttonStyle?: 0 | 1 | 2 // 0: black, 1: outline, 2: white
    }
  }
}


export interface Country {
  id: number;
  name: string;
}

export interface CustomerGroup {
  value: string;
  label: string;
}

export interface PaymentMethodsResponse {
  success: boolean;
  message?: string;
  data?: {
    methods: PaymentMethod[];
    countries: Country[];
    taxRulesGroups: any[];
    customerGroups: CustomerGroup[];
    onlyOrderMethods: string[];
    onlyPaymentsMethods: string[];
    environment: 'test' | 'live';
    is_connected: boolean;
  };
}


/**
 * Payment Methods API Service
 * Handles AJAX calls to AdminMolliePaymentMethods controller
 */
export class PaymentMethodsApiService {
  private baseUrl: string;

  constructor() {
    // Use PrestaShop's generated URL with proper tokens
    this.baseUrl = window.molliePaymentMethodsAjaxUrl || '';
  }

  /**
   * Get all payment methods with their configuration
   */
  async getPaymentMethods(): Promise<PaymentMethodsResponse> {
    const response = await fetch(`${this.baseUrl}&ajax=1&action=getPaymentMethods`, {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
      },
    });
    return response.json();
  }


  /**
   * Save payment method settings
   */
  async savePaymentMethodSettings(methodId: string, settings: PaymentMethod["settings"]): Promise<PaymentMethodsResponse> {
    const formData = new FormData();
    formData.append('ajax', '1');
    formData.append('action', 'savePaymentMethodSettings');
    formData.append('method_id', methodId);
    formData.append('settings', JSON.stringify(settings));

    const response = await fetch(this.baseUrl, {
      method: 'POST',
      body: formData
    });
    return response.json();
  }


  /**
   * Upload custom logo for card payment method
   */
  async uploadCustomLogo(file: File): Promise<{ success: boolean; message: string; logoUrl?: string }> {
    const formData = new FormData();
    formData.append('ajax', '1');
    formData.append('action', 'uploadCustomLogo');
    formData.append('fileToUpload', file);

    const response = await fetch(this.baseUrl, {
      method: 'POST',
      body: formData
    });
    return response.json();
  }
}

// Export singleton instance
export const paymentMethodsApiService = new PaymentMethodsApiService();