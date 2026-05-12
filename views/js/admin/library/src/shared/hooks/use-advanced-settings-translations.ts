import type { MollieAdvancedSettingsTranslations } from '../types';

/**
 * Hook to access PrestaShop advanced settings translations provided by the controller
 *
 * IMPORTANT: All translations MUST be provided by the backend controller using
 * PrestaShop's $this->module->l() method. No fallbacks should be used.
 */
export function useAdvancedSettingsTranslations() {
  // Get translations from PrestaShop backend (provided via Media::addJsDef)
  const translations = typeof window !== 'undefined' && window.mollieAdvancedSettingsTranslations
    ? window.mollieAdvancedSettingsTranslations
    : null;

  if (!translations) {
    console.error('Mollie Advanced Settings Translations not found. Ensure AdminMollieAdvancedSettingsController provides translations via Media::addJsDef.');
  }

  /**
   * Decode HTML entities in a string
   */
  const decodeHtmlEntities = (text: string): string => {
    const textarea = document.createElement('textarea');
    textarea.innerHTML = text;
    return textarea.value;
  };

  /**
   * Get translated string with placeholder replacement
   * @param key Translation key
   * @param replacements Optional replacements for placeholders like %s
   */
  const t = (key: keyof MollieAdvancedSettingsTranslations, ...replacements: string[]): string => {
    if (!translations) {
      console.warn(`Translation key "${key}" requested but translations not loaded`);
      return key; // Return key as fallback only for development
    }

    let translation = translations[key] || key;

    // Decode HTML entities (e.g., &quot; to ")
    translation = decodeHtmlEntities(translation);

    // Replace placeholders %s with provided replacements
    replacements.forEach((replacement) => {
      translation = translation.replace('%s', replacement);
    });

    return translation;
  };

  return { t, translations };
}
