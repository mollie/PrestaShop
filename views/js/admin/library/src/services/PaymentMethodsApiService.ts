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
  settings: {
    enabled: boolean
    title: string
    mollieComponents: boolean
    oneClickPayments: boolean
    transactionDescription: string
    apiSelection: "payments" | "orders"
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
  }
}

export interface Country {
  id: number
  name: string
}

export interface TaxRulesGroup {
  value: string
  label: string
}

export interface CustomerGroup {
  value: string
  label: string
}

export interface PaymentMethodsResponse {
  success: boolean;
  message?: string;
  data?: {
    methods: PaymentMethod[];
    countries: Country[];
    taxRulesGroups: TaxRulesGroup[];
    customerGroups: CustomerGroup[];
    onlyOrderMethods: string[];
    onlyPaymentsMethods: string[];
    environment: 'test' | 'live';
    is_connected: boolean;
  };
}

export interface UpdatePaymentMethodRequest {
  method_id: string;
  configuration: {
    title?: string;
    description?: string;
    min_amount?: string;
    max_amount?: string;
    surcharge_fixed?: string;
    surcharge_percentage?: string;
    surcharge_limit?: string;
    countries?: string[];
    excluded_countries?: string[];
    excluded_customer_groups?: number[];
    custom_logo?: any;
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
   * Toggle payment method enabled/disabled status
   */
  async togglePaymentMethod(methodId: string, enabled: boolean): Promise<PaymentMethodsResponse> {
    const formData = new FormData();
    formData.append('ajax', '1');
    formData.append('action', 'togglePaymentMethod');
    formData.append('method_id', methodId);
    formData.append('enabled', enabled.toString());

    const response = await fetch(this.baseUrl, {
      method: 'POST',
      body: formData
    });
    return response.json();
  }

  /**
   * Update payment method configuration
   */
  async updatePaymentMethod(request: UpdatePaymentMethodRequest): Promise<PaymentMethodsResponse> {
    const formData = new FormData();
    formData.append('ajax', '1');
    formData.append('action', 'updatePaymentMethod');
    formData.append('method_id', request.method_id);
    formData.append('configuration', JSON.stringify(request.configuration));

    const response = await fetch(this.baseUrl, {
      method: 'POST',
      body: formData
    });
    return response.json();
  }

  /**
   * Update payment methods order (drag-drop reordering)
   */
  async updateMethodsOrder(methodIds: string[]): Promise<PaymentMethodsResponse> {
    const formData = new FormData();
    formData.append('ajax', '1');
    formData.append('action', 'updateMethodsOrder');
    formData.append('method_ids', JSON.stringify(methodIds));

    const response = await fetch(this.baseUrl, {
      method: 'POST',
      body: formData
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
   * Refresh payment methods from Mollie API
   */
  async refreshMethods(): Promise<PaymentMethodsResponse> {
    const formData = new FormData();
    formData.append('ajax', '1');
    formData.append('action', 'refreshMethods');

    const response = await fetch(this.baseUrl, {
      method: 'POST',
      body: formData
    });
    return response.json();
  }
}

// Export singleton instance
export const paymentMethodsApiService = new PaymentMethodsApiService();