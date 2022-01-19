/**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 * @link        https://github.com/mollie/PrestaShop
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 * @codingStandardsIgnoreStart
 */
import React, { ChangeEvent, ReactElement, Suspense, lazy } from 'react';
import xss from 'xss';
import styled from 'styled-components';

import { IBanks, ITranslations } from '@shared/globals';
import LoadingDotsCentered from '@shared/components/LoadingDotsCentered';

const QrCode = lazy(() => import(/* webpackChunkName: "banks" */ '@qrcode/components/QrCode'));

const Radio = styled.div`
&&&& {
    padding-left: 80px;
    cursor: pointer;
    text-align: left;
}

&&&& label {
    display: inline-block;
    position: relative;
    padding-left: 5px;
    cursor: pointer;
}

&&&& label::before {
    content: "";
    display: inline-block;
    position: absolute;
    width: 17px;
    height: 17px;
    left: 0;
    top: 7px;
    margin-left: -20px;
    border: 1px solid #cccccc;
    border-radius: 50%;
    background-color: #fff;
    -webkit-transition: border 0.15s ease-in-out;
    -o-transition: border 0.15s ease-in-out;
    transition: border 0.15s ease-in-out;
}

&&&& label::after {
    display: inline-block;
    position: absolute;
    content: " ";
    width: 11px;
    height: 11px;
    left: 3px;
    top: 10px;
    margin-left: -20px;
    border-radius: 50%;
    background-color: #555555;
    -webkit-transform: scale(0, 0);
    -ms-transform: scale(0, 0);
    -o-transform: scale(0, 0);
    transform: scale(0, 0);
    -webkit-transition: -webkit-transform 0.1s cubic-bezier(0.8, -0.33, 0.2, 1.33);
    -moz-transition: -moz-transform 0.1s cubic-bezier(0.8, -0.33, 0.2, 1.33);
    -o-transition: -o-transform 0.1s cubic-bezier(0.8, -0.33, 0.2, 1.33);
    transition: transform 0.1s cubic-bezier(0.8, -0.33, 0.2, 1.33);
}

&&&& input[type="radio"] {
    opacity: 0;
}

&&&& input[type="radio"]:focus + label::before {
    outline: 5px auto -webkit-focus-ring-color;
    outline-offset: -2px;
}

&&&& input[type="radio"]:checked + label::after {
    -webkit-transform: scale(1, 1);
    -ms-transform: scale(1, 1);
    -o-transform: scale(1, 1);
    transform: scale(1, 1);
}

&&&& input[type="radio"]:disabled + label {
    opacity: 0.65;
}

&&&& input[type="radio"]:disabled + label::before {
    cursor: not-allowed;
}

&&&&&&&&-inline {
    margin-top: 0;
}

&&&& input[type="radio"] + label::after {
    background-color: #7cd1f9;
}

&&&& input[type="radio"]:checked + label::before {
    border-color: #7cd1f9;
}

&&&& input[type="radio"]:checked + label::after {
    background-color: #7cd1f9;
}
`;

declare let window: any;

interface IProps {
  banks: IBanks;
  translations: ITranslations;
  setIssuer: any;
}

export default function Banks({ banks, translations, setIssuer }: IProps): ReactElement<{}> {
  function _handleChange({ target: { value } }: ChangeEvent<HTMLInputElement>): void {
    setIssuer(value);
  }

  const firstBankId = (Object.values(banks))[0].id;

  return (
    <div>
      <ul>
        {Object.values(banks).map((bank) => (
          <Radio key={bank.id}>
            <input
              type="radio"
              defaultChecked={bank.id === firstBankId}
              id={bank.id}
              name="mollie-bank"
              value={bank.id}
              onChange={_handleChange}
            />
            <label htmlFor={bank.id} style={{ lineHeight: '24px' }}>
              <img
                src={bank.image.size2x}
                alt={xss(bank.name)}
                style={{ height: '24px', width: 'auto' }}
              /> {bank.name}
            </label>
          </Radio>
        ))}
      </ul>
      {window.mollieQrEnabled && (
        <Suspense fallback={<LoadingDotsCentered/>}>
          <QrCode title={translations.orPayByIdealQr} center/>
        </Suspense>
      )}
    </div>
  );
}
