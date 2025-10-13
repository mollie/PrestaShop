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
    // Force HTTPS if current page is HTTPS (fixes mixed content issues with proxies/ngrok)
    let url = window.mollieAuthAjaxUrl || '';
    if (window.location.protocol === 'https:' && url.startsWith('http:')) {
      url = url.replace('http:', 'https:');
    }
    this.baseUrl = url;
  }

  /**
   * Get current API key settings
   */
  async getCurrentSettings() {
    const url = new URL(this.baseUrl, window.location.origin);
    url.searchParams.set('ajax', '1');
    url.searchParams.set('action', 'getCurrentSettings');

    const response = await fetch(url.toString(), {
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
    const url = new URL(this.baseUrl, window.location.origin);

    const formData = new FormData();
    formData.append('ajax', '1');
    formData.append('action', 'saveApiKey');
    formData.append('api_key', apiKey);
    formData.append('environment', environment);

    const response = await fetch(url.toString(), {
      method: 'POST',
      body: formData
    });
    return response.json();
  }

  /**
   * Switch environment between test and live
   */
  async switchEnvironment(environment: string) {
    const url = new URL(this.baseUrl, window.location.origin);

    const formData = new FormData();
    formData.append('ajax', '1');
    formData.append('action', 'switchEnvironment');
    formData.append('environment', environment);

    const response = await fetch(url.toString(), {
      method: 'POST',
      body: formData
    });
    return response.json();
  }

  /**
   * Test API keys (both test and live)
   */
  async testApiKeys(testKey: string, liveKey: string) {
    const url = new URL(this.baseUrl, window.location.origin);

    const formData = new FormData();
    formData.append('ajax', '1');
    formData.append('action', 'testApiKeys');
    formData.append('testKey', testKey);  // Note: same parameter names as existing
    formData.append('liveKey', liveKey);

    const response = await fetch(url.toString(), {
      method: 'POST',
      body: formData
    });
    return response.json();
  }
}

// Export singleton instance
export const authApiService = new AuthenticationApiService();
