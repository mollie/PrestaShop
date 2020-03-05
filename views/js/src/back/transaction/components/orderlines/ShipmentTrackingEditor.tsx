/**
 * Copyright (c) 2012-2020, Mollie B.V.
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
import React, { ReactElement, useEffect, useState } from 'react';
import styled from 'styled-components';
import { get, isEmpty } from 'lodash';

import { IMollieOrderConfig, IMollieTracking, ITranslations } from '@shared/globals';
import {useMappedState} from "redux-react-hook";

interface IProps {
  edited: (newLines: IMollieTracking) => void;
  translations: ITranslations;
  config: IMollieOrderConfig;
  checkButtons: () => Promise<void> | void;
}


const ErrorMessage = styled.p`
margin-top: 2px;
visibility: ${({ show }: any) => show ? 'auto' : 'hidden'};
color: #f00;
` as any;

const FormGroup = styled.div`
min-height: 60px!important;
` as any;

const Label = styled.label`
font-size: medium!important;
text-align: left!important;
` as any;

const Input = styled.input`
font-size: medium!important;
text-align: left!important;
` as any;

const InputContainer = styled.div`
text-align: left!important;
`;

export default function ShipmentTrackingEditor(props: IProps): ReactElement<{}> {
  const [skipTracking, setSkipTracking] = useState<boolean>(false);
  const [carrier, setCarrier] = useState<string>(get(props, 'config.tracking.tracking.carrier', ''));
  const [carrierChanged, setCarrierChanged] = useState<boolean>(!!get(props, 'config.tracking.tracking.carrier', false));
  const [code, setCode] = useState<string>(get(props, 'config.tracking.tracking.code', ''));
  const [codeChanged, setCodeChanged] = useState<boolean>(!!get(props, 'config.tracking.tracking.code', ''));
  const [url, setUrl] = useState<string>(get(props, 'config.tracking.tracking.url', ''));
  const { translations, edited } = props;
  function _getCarrierInvalid(): boolean {
    return !skipTracking && isEmpty(carrier.replace(/\s+/, '')) && carrierChanged;
  }

  function _getCodeInvalid(): boolean {
    return !skipTracking && isEmpty(code.replace(/\s+/, '')) && codeChanged;
  }

  function _updateSkipTracking(skipTracking: boolean): void {
    setSkipTracking(skipTracking);
    edited(skipTracking ? null : {
      carrier,
      code,
      url,
    });
  }

  function _updateCarrier(carrier: string): void {
    setCarrier(carrier);
    setCarrierChanged(true);

    edited({
      carrier,
      code,
      url,
    });
  }

  function _updateCode(code: string): void {
    setCode(code);
    setCodeChanged(true);
    edited({
      carrier,
      code,
      url,
    });
  }

  function _updateUrl(url: string): void {
    setUrl(url);
    edited({ carrier, code, url });
  }

  useEffect(() => {
    const { edited } = props;

    if (carrierChanged) {
      edited(skipTracking ? null : {
        carrier: carrier,
        code: code,
        url: url,
      });
    }
  }, []);

  return (
    <div style={{ textAlign: 'left' }}>
      <Label htmlFor="skipTracking">
        <Input
          id="skipTracking"
          name="skipTracking"
          type="checkbox"
          checked={skipTracking}
          onChange={({ target: { checked: skipTracking } }: any) => _updateSkipTracking(skipTracking)}
        />
        <span>&nbsp;{translations.skipTrackingDetails}</span>
      </Label>
      <br/>
      <br/>
      <FormGroup>
        <Label htmlFor="input-carrier"><span>{translations.carrier} <sup>*</sup></span></Label>
        <InputContainer>
          <Input
            type="text"
            placeholder={translations.egFedex}
            className="l-form-control"
            name="carrier"
            id="input-carrier"
            disabled={skipTracking}
            value={carrier}
            onChange={({ target: { value: carrier } }: any) => _updateCarrier(carrier)}
          />
          <ErrorMessage show={_getCarrierInvalid()}>
            {translations.thisInfoIsRequired}
          </ErrorMessage>
        </InputContainer>
      </FormGroup>
      <FormGroup>
        <Label htmlFor="input-code"><span>{translations.trackingCode} <sup>*</sup></span></Label>
        <InputContainer>
          <Input
            type="text"
            name="code"
            id="input-code"
            value={code}
            disabled={skipTracking}
            onChange={({ target: { value: code } }: any) => _updateCode(code)}
          />
          <ErrorMessage show={_getCodeInvalid()}>
            {translations.thisInfoIsRequired}
          </ErrorMessage>
        </InputContainer>
      </FormGroup>
      <FormGroup>
        <Label htmlFor="input-url"><span><span>{translations.url}</span> ({translations.optional})</span></Label>
        <InputContainer>
          <Input
            type="text"
            className="l-form-control"
            placeholder="https://"
            name="url"
            id="input-url"
            value={url}
            disabled={skipTracking}
            onChange={({ target: { value: url } }: any) => _updateUrl(url)}
          />
        </InputContainer>
      </FormGroup>
    </div>
  );
}
