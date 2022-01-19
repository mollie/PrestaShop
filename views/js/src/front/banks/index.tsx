/**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 * @link        https://github.com/mollie/PrestaShop
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 * @codingStandardsIgnoreStart
 */
import React, { lazy, Suspense } from 'react';
import { render, unmountComponentAtNode } from 'react-dom';
import swal from 'sweetalert';
import xss from 'xss';

import { IBanks, ITranslations } from '@shared/globals';
import LoadingDotsCentered from '@shared/components/LoadingDotsCentered';

const Banks = lazy(() => import(/* webpackPrefetch: true, webpackChunkName: "banks" */ '@banks/components/Banks'));

declare let window: any;

export default function bankList(banks: IBanks, translations: ITranslations): void {
  let issuer = Object.values(banks)[0].id;
  function _setIssuer(newIssuer: string): void {
    issuer = newIssuer;
  }

  (async function () {
    const wrapper = document.createElement('DIV');
    render(
      (
        <div>
          <Suspense fallback={<LoadingDotsCentered/>}>
            <Banks banks={banks} translations={translations} setIssuer={_setIssuer}/>
          </Suspense>
        </div>
      ),
      wrapper
    );
    const elem = wrapper.firstChild as Element;

    const value = await swal({
      title: xss(translations.chooseYourBank),
      content: {
        element: elem,
      },
      buttons: {
        cancel: {
          text: xss(translations.cancel),
          visible: true,
        },
        confirm: {
          text: xss(translations.choose),
        },
      },
    });
    if (value) {
      const win = window.open(banks[issuer].href, '_self');
      win.opener = null;
    } else {
      try {
        setTimeout(() => unmountComponentAtNode(wrapper), 2000);
      } catch (e) {
      }
    }
  }());
}

