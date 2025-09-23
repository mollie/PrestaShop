import type { MollieAuthTranslations } from '../types';

/**
 * Hook to access PrestaShop translations provided by the controller
 * 
 * IMPORTANT: All translations MUST be provided by the backend controller using 
 * PrestaShop's $this->module->l() method. No fallbacks should be used.
 */
export function useTranslations() {
  // Get translations from PrestaShop backend (provided via Media::addJsDef)
  const translations = typeof window !== 'undefined' && window.mollieAuthTranslations 
    ? window.mollieAuthTranslations 
    : null;

  if (!translations) {
    console.error('Mollie Auth Translations not found. Ensure AdminMollieAuthenticationController provides translations via Media::addJsDef.');
  }

  /**
   * Get translated string with placeholder replacement
   * @param key Translation key
   * @param replacements Optional replacements for placeholders like %s
   */
  const t = (key: keyof MollieAuthTranslations, ...replacements: string[]): string => {
    if (!translations) {
      console.warn(`Translation key "${key}" requested but translations not loaded`);
      return key; // Return key as fallback only for development
    }

    let translation = translations[key] || key;
    
    // Replace placeholders %s with provided replacements
    replacements.forEach((replacement) => {
      translation = translation.replace('%s', replacement);
    });
    
    return translation;
  };

  return { t, translations };
}
