/**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 * @link        https://github.com/mollie/PrestaShop
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 * @codingStandardsIgnoreStart
 */
import React from 'react';
import { render } from 'react-dom';

export default function (target: string|HTMLElement, title: string, center: boolean): void {
  const elem = (typeof target === 'string' ? document.getElementById(target) : target);
  (async function () {
    const [
      { default: QrCode },
    ] = await Promise.all([
      import(/* webpackChunkName: "banks" */ '@qrcode/components/QrCode'),
    ]);
    render(
      <QrCode title={title} center={center}/>,
      elem
    );
  }());

}
