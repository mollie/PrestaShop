// Declare global variable from PrestaShop
declare global {
  interface Window {
    molliePaymentMethodsAjaxUrl: string;
    mollieAjaxUrl: string;
    molliePaymentMethodsConfig?: {
      countries: Country[];
      taxRulesGroups: { value: string; label: string }[];
      customerGroups: CustomerGroup[];
      onlyOrderMethods: string[];
      onlyPaymentsMethods: string[];
    };
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
    voucherCategory?: "none" | "meal" | "gift" | "eco" | "all"
    paymentRestrictions: {
      acceptFrom: string
      selectedCountries?: string[]
      excludeCountries: string[]
      excludeCustomerGroups: string[]
    }
    paymentFees: {
      enabled: boolean
      type: "none" | "fixed" | "percentage" | "combined"
      taxGroup: string
      // Fixed fee fields
      fixedFeeTaxIncl: string
      fixedFeeTaxExcl: string
      // Percentage fee fields
      percentageFee: string
      maxFeeCap: string
    }
    orderRestrictions: {
      minAmount: string
      maxAmount: string
      apiMinAmount: string | null
      apiMaxAmount: string | null
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
    taxRulesGroups: { value: string; label: string }[];
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
    // Force HTTPS if current page is HTTPS (fixes mixed content issues with proxies/ngrok)
    let url = window.molliePaymentMethodsAjaxUrl || '';
    if (window.location.protocol === 'https:' && url.startsWith('http:')) {
      url = url.replace('http:', 'https:');
    }
    this.baseUrl = url;
  }

  /**
   * Get all payment methods with their configuration
   */
  async getPaymentMethods(): Promise<PaymentMethodsResponse> {
    const url = new URL(this.baseUrl, window.location.origin);
    url.searchParams.set('ajax', '1');
    url.searchParams.set('action', 'getPaymentMethods');

    const response = await fetch(url.toString(), {
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
    const url = new URL(this.baseUrl, window.location.origin);

    const formData = new FormData();
    formData.append('ajax', '1');
    formData.append('action', 'savePaymentMethodSettings');
    formData.append('method_id', methodId);
    formData.append('settings', JSON.stringify(settings));

    const response = await fetch(url.toString(), {
      method: 'POST',
      body: formData
    });
    return response.json();
  }


  /**
   * Upload custom logo for card payment method
   */
  async uploadCustomLogo(file: File): Promise<{ success: boolean; message: string; logoUrl?: string }> {
    const url = new URL(this.baseUrl, window.location.origin);

    const formData = new FormData();
    formData.append('ajax', '1');
    formData.append('action', 'uploadCustomLogo');
    formData.append('fileToUpload', file);

    const response = await fetch(url.toString(), {
      method: 'POST',
      body: formData
    });
    return response.json();
  }

  /**
   * Update payment methods order (drag & drop reordering)
   */
  async updateMethodsOrder(methodIds: string[]): Promise<PaymentMethodsResponse> {
    const url = new URL(this.baseUrl, window.location.origin);

    const formData = new FormData();
    formData.append('ajax', '1');
    formData.append('action', 'updateMethodsOrder');
    formData.append('method_ids', JSON.stringify(methodIds));

    const response = await fetch(url.toString(), {
      method: 'POST',
      body: formData
    });
    return response.json();
  }

  /**
   * Calculate payment fee tax (tax incl/excl conversion)
   * Uses existing AdminMollieAjax endpoint
   */
  async calculatePaymentFeeTax(
    paymentFeeTaxIncl: string,
    paymentFeeTaxExcl: string,
    taxRulesGroupId: string
  ): Promise<{ error: boolean; message?: string; paymentFeeTaxIncl?: string; paymentFeeTaxExcl?: string }> {
    let ajaxUrl = window.mollieAjaxUrl || '';

    if (window.location.protocol === 'https:' && ajaxUrl.startsWith('http:')) {
      ajaxUrl = ajaxUrl.replace('http:', 'https:');
    }

    const formData = new FormData();
    formData.append('action', 'updateFixedPaymentFeePrice');
    formData.append('paymentFeeTaxIncl', paymentFeeTaxIncl);
    formData.append('paymentFeeTaxExcl', paymentFeeTaxExcl);
    formData.append('taxRulesGroupId', taxRulesGroupId);
    formData.append('ajax', '1');

    const response = await fetch(ajaxUrl, {
      method: 'POST',
      body: formData
    });

    return response.json();
  }
}

// Export singleton instance
export const paymentMethodsApiService = new PaymentMethodsApiService();