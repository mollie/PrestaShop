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
import React, { Component } from 'react';
import styled from 'styled-components';
import _ from 'lodash';
import { IMollieOrderConfig, IMollieTracking, ITranslations } from '../../../../globals';

interface IProps {
  edited: (newLines: IMollieTracking) => void;
  translations: ITranslations;
  config: IMollieOrderConfig;
  checkButtons: () => Promise<void>|void;
}

interface IState {
  skipTracking: boolean,
  carrier: string,
  carrierChanged: boolean,
  code: string,
  codeChanged: boolean,
  url: string,
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

class ShipmentTrackingEditor extends Component<IProps> {
  readonly state: IState = {
    skipTracking: false,
    carrier: _.get(this.props, 'config.tracking.carrier', ''),
    carrierChanged: !!_.get(this.props, 'config.tracking.carrier', false),
    code: _.get(this.props, 'config.tracking.code', ''),
    codeChanged: !!_.get(this.props, 'config.tracking.code', false),
    url: _.get(this.props, 'config.tracking.url', ''),
  };

  componentDidMount() {
    const { carrierChanged, carrier, code, url, skipTracking } = this.state;
    const { edited } = this.props;

    if (carrierChanged) {
      edited(skipTracking ? null : {
        carrier: carrier,
        code: code,
        url: url,
      });
    }
  }

  get carrierInvalid() {
    const { skipTracking, carrier, carrierChanged } = this.state;

    return !skipTracking && _.isEmpty(carrier.replace(/\s+/, '')) && carrierChanged;
  }

  get codeInvalid() {
    const { skipTracking, code, codeChanged } = this.state;

    return !skipTracking && _.isEmpty(code.replace(/\s+/, '')) && codeChanged;
  }

  updateSkipTracking = (skipTracking: boolean): void => {
    const { edited } = this.props;
    this.setState({ skipTracking });
    edited(skipTracking ? null : {
      carrier: this.state.carrier,
      code: this.state.code,
      url: this.state.url,
    });
  };

  updateCarrier = (carrier: string): void => {
    const { edited } = this.props;
    const { code, url } = this.state;

    this.setState({
      carrier,
      carrierChanged: true,
    });
    edited({
      carrier,
      code,
      url,
    });
  };

  updateCode = (code: string): void => {
    const { edited } = this.props;
    const { carrier, url } = this.state;

    this.setState({
      code,
      codeChanged: true,
    });
    edited({
      carrier,
      code,
      url,
    });
  };

  updateUrl = (url: string): void => {
    const { edited } = this.props;
    const { code, carrier } = this.state;

    this.setState({ url });
    edited({ carrier, code, url });
  };

  render() {
    const { skipTracking, carrier, code, url } = this.state;
    const { translations } = this.props;

    return (
      <div style={{ textAlign: 'left' }}>
        <Label htmlFor="skipTracking">
          <Input
            id="skipTracking"
            name="skipTracking"
            type="checkbox"
            checked={skipTracking}
            onChange={({ target: { checked: skipTracking } }: any) => this.updateSkipTracking(skipTracking)}
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
              onChange={({ target: { value: carrier } }: any) => this.updateCarrier(carrier)}
            />
            <ErrorMessage show={this.carrierInvalid}>
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
              onChange={({ target: { value: code } }: any) => this.updateCode(code)}
            />
            <ErrorMessage show={this.codeInvalid}>
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
              onChange={({ target: { value: url } }: any) => this.updateUrl(url)}
            />
          </InputContainer>
        </FormGroup>
      </div>
    )
  }
}

export default ShipmentTrackingEditor;
