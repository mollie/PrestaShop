/**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 * @link        https://github.com/mollie/PrestaShop
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 * @codingStandardsIgnoreStart
 */
/*eslint @typescript-eslint/camelcase:off, eqeqeq:off */
import version_compare from 'locutus/php/info/version_compare';
import { get } from 'lodash';

import { ICurrency } from './globals';

declare let window: any;

function ceilf(value: number, precision?: number): number {
  if (typeof precision === 'undefined') {
    precision = 0;
  }

  const precisionFactor = precision === 0 ? 1 : Math.pow(10, precision);
  const tmp = value * precisionFactor;
  const tmp2 = String(tmp);
  if (tmp2[tmp2.length - 1] === '0') {
    return value;
  }

  return Math.ceil(value * precisionFactor) / precisionFactor;
}

function floorf(value: number, precision?: number): number {
  if (typeof precision === 'undefined') {
    precision = 0;
  }

  const precisionFactor = precision === 0 ? 1 : Math.pow(10, precision);
  const tmp = value * precisionFactor;
  const tmp2 = String(tmp);
  if (tmp2[tmp2.length - 1] === '0') {
    return value;
  }

  return Math.floor(value * precisionFactor) / precisionFactor;
}

export const formattedNumberToFloat = (price: string | number, currencyFormat: number, currencySign: string): number|string => {
  if (typeof price === 'number') {
    price = String(price);
  }
  price = price.replace(currencySign, '');
  if (currencyFormat === 1) {
    return parseFloat(price.replace(',', '').replace(' ', ''));
  } else if (currencyFormat === 2) {
    return parseFloat(price.replace(' ', '').replace(',', '.'));
  } else if (currencyFormat === 3) {
    return parseFloat(price.replace('.', '').replace(' ', '').replace(',', '.'));
  } else if (currencyFormat === 4) {
    return parseFloat(price.replace(',', '').replace(' ', ''));
  }

  return price;
};

// Return a formatted number
export const formatNumber = (value: number | string, numberOfDecimal: number | string, thousenSeparator: string, virgule: string): string => {
  if (typeof numberOfDecimal === 'string') {
    numberOfDecimal = parseInt(numberOfDecimal, 10);
  }
  if (typeof value === 'string') {
    value = parseFloat(value);
  }
  value = value.toFixed(numberOfDecimal);
  const valString: string = value + '';
  const tmp = valString.split('.');
  let absValString = (tmp.length === 2) ? tmp[0] : valString;
  const deciString: string = ('0.' + (tmp.length === 2 ? tmp[1] : 0)).substr(2);
  const nb = absValString.length;

  for (let i = 1; i < 4; i++) {
    if (parseFloat(value) >= Math.pow(10, (3 * i))) {
      absValString = absValString.substring(0, nb - (3 * i)) + thousenSeparator + absValString.substring(nb - (3 * i));
    }
  }

  if (numberOfDecimal === 0) {
    return absValString;
  }

  return absValString + virgule + (deciString ? deciString : '00');
};

function psRoundHelper(value: number, mode: number): number {
  // From PHP Math.c
  let tmpValue;
  if (value >= 0.0) {
    tmpValue = Math.floor(value + 0.5);
    if ((mode === 3 && value === (-0.5 + tmpValue)) ||
      (mode === 4 && value === (0.5 + 2 * Math.floor(tmpValue / 2.0))) ||
      (mode === 5 && value === (0.5 + 2 * Math.floor(tmpValue / 2.0) - 1.0))) {
      tmpValue -= 1.0;
    }
  } else {
    tmpValue = Math.ceil(value - 0.5);
    if ((mode === 3 && value === (0.5 + tmpValue)) ||
      (mode === 4 && value === (-0.5 + 2 * Math.ceil(tmpValue / 2.0))) ||
      (mode === 5 && value === (-0.5 + 2 * Math.ceil(tmpValue / 2.0) + 1.0))) {
      tmpValue += 1.0;
    }
  }

  return tmpValue;
}

function psLog10(value: number): number {
  return Math.log(value) / Math.LN10;
}

function psRoundHalfUp(value: number, precision: number): number {
  const mul = Math.pow(10, precision);
  let val = value * mul;

  const nextDigit = Math.floor(val * 10) - 10 * Math.floor(val);
  if (nextDigit >= 5) {
    val = Math.ceil(val);
  } else {
    val = Math.floor(val);
  }

  return val / mul;
}

export const psRound = (value: number | string, places?: number): number => {
  let method;
  if (typeof value === 'string') {
    value = parseFloat(value);
  }
  if (typeof window.roundMode === 'undefined') {
    method = 2;
  } else {
    method = window.roundMode;
  }
  if (typeof places === 'undefined') {
    places = 2;
  }

  let tmpValue;

  if (method === 0) {
    return ceilf(value, places);
  } else if (method === 1) {
    return floorf(value, places);
  } else if (method === 2) {
    return psRoundHalfUp(value, places);
  } else if (method == 3 || method == 4 || method == 5) {
    // From PHP Math.c
    const precision_places = 14 - Math.floor(psLog10(Math.abs(value)));
    const f1 = Math.pow(10, Math.abs(places));

    if (precision_places > places && precision_places - places < 15) {
      let f2 = Math.pow(10, Math.abs(precision_places));
      if (precision_places >= 0) {
        tmpValue = value * f2;
      } else {
        tmpValue = value / f2;
      }

      tmpValue = psRoundHelper(tmpValue, method);

      /* now correctly move the decimal point */
      f2 = Math.pow(10, Math.abs(places - precision_places));
      /* because places < precision_places */
      tmpValue /= f2;
    } else {
      /* adjust the value */
      if (places >= 0) {
        tmpValue = value * f1;
      } else {
        tmpValue = value / f1;
      }

      if (Math.abs(tmpValue) >= 1e15) {
        return value;
      }
    }

    tmpValue = psRoundHelper(tmpValue, method);
    if (places > 0) {
      tmpValue = tmpValue / f1;
    } else {
      tmpValue = tmpValue * f1;
    }

    return tmpValue;
  }
};

export const getNumberFormat = (currencyFormat: number | string): any => {
  if (typeof currencyFormat === 'string') {
    currencyFormat = parseInt(currencyFormat, 10);
  }
  switch (currencyFormat) {
    case 1:
      return {
        comma: '.',
        thousands: ',',
      };
    case 2:
      return {
        comma: ',',
        thousands: ' ',
      };
    case 4:
      return {
        comma: '.',
        thousands: ',',
      };
    case 5:
      return {
        comma: '.',
        thousands: '\'',
      };
    default:
      return {
        comma: ',',
        thousands: '.',
      };
  }
};

export const findGetParameter = (parameterName: string): any => {
  let result = null;
  let tmp = [];
  const items = window.location.search.substr(1).split('&');
  for (let index = 0; index < items.length; index++) {
    tmp = items[index].split('=');
    if (tmp[0] === parameterName) {
      result = decodeURIComponent(tmp[1]);
    }
  }

  return result;
};

/**
 * Filter money value (EUR / 2 decimals)
 *
 * @param val {number|string}
 *
 * @returns {number}
 */
export const formatMoneyValue = (val: string | number): number => {
  if (typeof val === 'string') {
    val = parseFloat(val.replace(/,/, '.'));
  }
  if (typeof val !== 'number' || isNaN(val)) {
    val = 0;
  }

  return val;
};

export const psFormatCurrency = (price: string | number, currencyFormat: number, currencySign: string, currencyBlank: string): string => {
  if (typeof price === 'string') {
    price = parseFloat(price);
  }
  let currency = 'EUR';
  if (typeof window.currency_iso_code !== 'undefined' && window.currency_iso_code.length === 3) {
    currency = window.currency_iso_code;
  } else if (typeof window.currency === 'object'
    && typeof window.currency.iso_code !== 'undefined'
    && window.currency.iso_code.length === 3
  ) {
    currency = window.currency.iso_code;
  }

  let displayPrecision;
  if (typeof window.priceDisplayPrecision !== 'undefined') {
    displayPrecision = window.priceDisplayPrecision;
  }

  try {
    if (typeof window.currencyModes !== 'undefined'
      && typeof window.currencyModes[currency] !== 'undefined'
      && window.currencyModes[currency]
    ) {
      price = psRound(price.toFixed(10), displayPrecision);
      let locale = document.documentElement.lang;
      if (locale.length === 5) {
        locale = locale.substring(0, 2).toLowerCase() + '-' + locale.substring(3, 5).toUpperCase();
      } else if (typeof window.full_language_code !== 'undefined' && window.full_language_code.length === 5) {
        locale = window.full_language_code.substring(0, 2).toLowerCase() + '-' + window.full_language_code.substring(3, 5).toUpperCase();
      } else if (window.getBrowserLocale().length === 5) {
        locale = window.getBrowserLocale().substring(0, 2).toLowerCase() + '-' + window.getBrowserLocale().substring(3, 5).toUpperCase();
      }

      let formattedCurrency = price.toLocaleString(locale, {
        style: 'currency',
        currency: 'USD',
        currencyDisplay: 'code',
      });
      if (currencySign) {
        formattedCurrency = formattedCurrency.replace('USD', currencySign);
      }

      return formattedCurrency;
    }
  } catch (e) {
    // Just continue, Intl data is not available on every browser and crashes
  }

  let blank = '';

  price = psRound(price.toFixed(10), displayPrecision);
  if (typeof currencyBlank !== 'undefined' && currencyBlank) {
    blank = ' ';
  }

  if (currencyFormat == 1) {
    return currencySign + blank + formatNumber(price, displayPrecision, ',', '.');
  }
  if (currencyFormat == 2) {
    return (formatNumber(price, displayPrecision, ' ', ',') + blank + currencySign);
  }
  if (currencyFormat == 3) {
    return (currencySign + blank + formatNumber(price, displayPrecision, '.', ','));
  }
  if (currencyFormat == 4) {
    return (formatNumber(price, displayPrecision, ',', '.') + blank + currencySign);
  }
  if (currencyFormat == 5) {
    return (currencySign + blank + formatNumber(price, displayPrecision, '\'', '.'));
  }

  return String(price);
};

export const formatCurrency = (price: number, currency: ICurrency): string => {
  if (typeof currency === 'undefined') {
    console.error('Currency undefined');
    return '';
  }

  if ((typeof window._PS_VERSION_ === 'string' && version_compare(window._PS_VERSION_, '1.7.0.0', '>='))
    || typeof window.formatCurrencyCldr !== 'undefined'
  ) {
    // PrestaShop 1.7 CLDR
    return (new Intl.NumberFormat(get(document.documentElement, 'lang'), { style: 'currency', currency: currency.iso_code })).format(price);
  }

  return psFormatCurrency(price, currency.format, currency.sign, currency.blank);
};
