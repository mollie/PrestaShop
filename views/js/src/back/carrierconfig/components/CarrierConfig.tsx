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
import React, { Component } from 'react';
import _ from 'lodash';
import classnames from 'classnames';
import axios from 'axios';
import { connect } from 'react-redux';
import Error from './Error';
import LoadingDots from '../../misc/components/LoadingDots';
import { Dispatch } from 'redux';
import { updateCarriers } from '../store/actions';

interface IProps {
  config: IMollieCarrierConfig,
  translations: ITranslations,
  target: string,

  // Redux
  carriers?: Array<IMollieCarrierConfigItem>,
  dispatchUpdateCarriers?: Function,
}

class CarrierConfig extends Component<IProps> {
  componentDidMount() {
    this.init();
  }

  get carrierConfig() {
    const carriers: IMollieCarrierConfigItems = {};
    _.forEach(this.props.carriers, (carrier) => {
      carriers[carrier.id_carrier] = carrier;
    });

    return carriers;
  }

  init = () => {
    const self = this;
    const { config: { ajaxEndpoint }, carriers } = this.props;
    if (carriers === null) {
      setTimeout(async () => {
        try {
          const { data: { carriers } = { carriers: null } } = await axios.get(ajaxEndpoint);
          self.props.dispatchUpdateCarriers(carriers);
        } catch (e) {
          console.error(e);
        }
      }, 0);
    }
  };

  updateCarrierConfig = (id: string, key: string, value: string|null) => {
    const localConfig = _.cloneDeep(this.props.carriers);

    const config = _.find(localConfig, item => item.id_carrier === id);
    if (typeof config === 'undefined') {
      return;
    }
    config[key] = value;

    this.props.dispatchUpdateCarriers(localConfig);
  };

  render() {
    const { translations, target, config: { legacy }, carriers } = this.props;

    if (_.isArray(carriers) && _.isEmpty(carriers)) {
      return <Error retry={this.init}/>;
    }

    if (carriers === null) {
      return <LoadingDots/>;
    }

    return (
      <>
        <div className={classnames({
          'alert': !legacy,
          'alert-info': !legacy,
          'warn': legacy,
        })}
        >
          {translations.hereYouCanConfigureCarriers}
          <br/>{translations.youCanUseTheFollowingVariables}
          <ul>
            <li><strong>@ </strong>: {translations.shippingNumber}</li>
            <li><strong>%%shipping_number%% </strong>: {translations.shippingNumber}</li>
            <li><strong>%%invoice.country_iso%%</strong>: {translations.invoiceCountryCode}</li>
            <li><strong>%%invoice.postcode%% </strong>: {translations.invoicePostcode}</li>
            <li><strong>%%delivery.country_iso%%</strong>: {translations.deliveryCountryCode}</li>
            <li><strong>%%delivery.postcode%% </strong>: {translations.deliveryPostcode}</li>
            <li><strong>%%lang_iso%% </strong>: {translations.languageCode}</li>
          </ul>
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
            {carriers.map((carrier) => (
              <tr key={carrier.id_carrier}>
                <td className="left">
                  {carrier.name}
                </td>
                <td className="left">
                  <select
                    value={carrier.source}
                    onChange={({ target: { value } }) => this.updateCarrierConfig(carrier.id_carrier, 'source', value)}
                  >
                    <option value="do_not_auto_ship">{translations.doNotAutoShip}</option>
                    <option value="no_tracking_info">{translations.noTrackingInformation}</option>
                    <option value="carrier_url">{translations.carrierUrl}</option>
                    <option value="custom_url">{translations.customUrl}</option>
                    {carrier.module && <option value="module">{translations.module}</option>}
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
        <input type="hidden" id={target} name={target} value={JSON.stringify(this.carrierConfig)}/>
      </>
    );
  }
}

export default connect<{}, {}, IProps>(
  (state: IMollieCarriersState): Partial<IProps> => ({
    carriers: state.carriers,
  }),
  (dispatch: Dispatch): Partial<IProps> => ({
    dispatchUpdateCarriers(carriers: Array<IMollieCarrierConfigItem>) {
      dispatch(updateCarriers(carriers))
    }
  })
)(CarrierConfig);
