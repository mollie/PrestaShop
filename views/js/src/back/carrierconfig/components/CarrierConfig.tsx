/**
 * Copyright (c) 2012-2018, Mollie B.V.
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
import React, { Component, Fragment } from 'react';
import _ from 'lodash';
import classnames from 'classnames';

interface IProps {
  config: IMollieCarrierConfig,
  translations: ITranslations,
  target: string,
}

interface IState {
  carrierConfig: Array<IMollieCarrierConfigItem>,
}

class CarrierConfig extends Component<IProps> {
  state: IState = {
    carrierConfig: this.props.config.carrierConfig,
  };

  updateCarrierConfig = (id: string, key: string, value: string|null) => {
    const localConfig = _.cloneDeep(this.state.carrierConfig);

    const config = _.find(localConfig, item => item.id_carrier === id);
    if (typeof config === 'undefined') {
      return;
    }
    config[key] = value;

    this.setState(() => ({
      config: localConfig,
    }));
  };

  render() {
    const { carrierConfig } = this.state;
    const { translations, target, config: { legacy } } = this.props;

    return (
      <Fragment>
        <div className={classnames({
          'alert': !legacy,
          'alert-info': !legacy,
          'warn': legacy,
        })}
        >
          Check the source of your carrier
        </div>
        <table className="list form alternate table">
          <thead>
            <tr>
              <td className="left">{translations.name}</td>
              <td className="left">{translations.urlSource}</td>
              <td className="left">{translations.customUrl}</td>
            </tr>
          </thead>
          <tbody>
            {carrierConfig.map((carrier) => (
              <tr key={carrier.id_carrier}>
                <td className="left">
                  {carrier.name}
                </td>
                <td className="left">
                  <select
                    value={carrier.source}
                    onChange={({ target: { value } }) => this.updateCarrierConfig(carrier.id_carrier, 'source', value)}
                  >
                    <option value="carrier_url">{translations.carrierUrl}</option>
                    <option value="custom_url">{translations.customUrl}</option>
                    <option value="module">{translations.module}</option>
                  </select>
                </td>
                <td className="left">
                  <input
                    type="text"
                    disabled={carrier.source !== 'custom_url'}
                    value={carrier.custom_url}
                    onChange={({ target: { value } }) => this.updateCarrierConfig(carrier.id_carrier, 'custom_url', value)}
                  />
                </td>
              </tr>
            ))}
          </tbody>
        </table>
        <input type="hidden" id={target} name={target} value={JSON.stringify(this.state.carrierConfig)}/>
      </Fragment>
    );
  }
}

export default CarrierConfig;
