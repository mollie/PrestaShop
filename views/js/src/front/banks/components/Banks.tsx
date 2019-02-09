/**
 * Copyright (c) 2012-2019, Mollie B.V.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * - Redistributions of source code must retain the above copyright notice,
 *    this list of conditions and the following disclaimer.
 * - Redistributions in binary form must reproduce the above copyright
 *    notice, this list of conditions and the following disclaimer in the
 *    documentation and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE AUTHOR AND CONTRIBUTORS ``AS IS'' AND ANY
 * EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE AUTHOR OR CONTRIBUTORS BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY
 * OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH
 * DAMAGE.
 *
 * @author     Mollie B.V. <info@mollie.nl>
 * @copyright  Mollie B.V.
 * @license    Berkeley Software Distribution License (BSD-License 2) http://www.opensource.org/licenses/bsd-license.php
 * @category   Mollie
 * @package    Mollie
 * @link       https://www.mollie.nl
 */
import React, { ChangeEvent } from 'react';
import xss from 'xss';
import styled from 'styled-components';

import { IBanks, ITranslations } from '../../../globals';
import QrCode from '../../qrcode/components/QrCode';

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

export default function Banks({ banks, translations, setIssuer }: IProps) {
  function _handleChange({ target: { value } }: ChangeEvent<HTMLInputElement>) {
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
      {window.mollieQrEnabled && <QrCode title={translations.orPayByIdealQr} center/>}
    </div>
  );
}
