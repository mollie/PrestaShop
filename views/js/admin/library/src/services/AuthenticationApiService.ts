// Declare global variable from PrestaShop
declare global {
  interface Window {
    mollieAuthAjaxUrl: string;
  }
}

/**
 * Authentication API Service
 * Handles AJAX calls to AdminMollieAuthentication controller
 */
export class AuthenticationApiService {
  private baseUrl: string;

  constructor() {
    // Use PrestaShop's generated URL with proper tokens
    this.baseUrl = window.mollieAuthAjaxUrl || '';
  }

  /**
   * Get current API key settings
   */
  async getCurrentSettings() {
    const response = await fetch(`${this.baseUrl}&ajax=1&action=getCurrentSettings`, {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
      },
    });
    return response.json();
  }

  /**
   * Save API key for specific environment
   */
  async saveApiKey(apiKey: string, environment: string) {
    const formData = new FormData();
    formData.append('ajax', '1');
    formData.append('action', 'saveApiKey');
    formData.append('api_key', apiKey);
    formData.append('environment', environment);

    const response = await fetch(this.baseUrl, {
      method: 'POST',
      body: formData
    });
    return response.json();
  }

  /**
   * Switch environment between test and live
   */
  async switchEnvironment(environment: string) {
    const formData = new FormData();
    formData.append('ajax', '1');
    formData.append('action', 'switchEnvironment');
    formData.append('environment', environment);
    
    const response = await fetch(this.baseUrl, {
      method: 'POST',
      body: formData
    });
    return response.json();
  }

  /**
   * Test API keys (both test and live)
   */
  async testApiKeys(testKey: string, liveKey: string) {
    const formData = new FormData();
    formData.append('ajax', '1');
    formData.append('action', 'testApiKeys');
    formData.append('testKey', testKey);  // Note: same parameter names as existing
    formData.append('liveKey', liveKey);

    const response = await fetch(this.baseUrl, {
      method: 'POST',
      body: formData
    });
    return response.json();
  }
}

// Export singleton instance
export const authApiService = new AuthenticationApiService();
