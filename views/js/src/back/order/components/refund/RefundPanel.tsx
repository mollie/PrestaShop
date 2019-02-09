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
import React from 'react';
import { connect } from 'react-redux';
import { Dispatch } from 'redux';
import { updatePayment } from '../../store/actions';
import LoadingDots from '../../../misc/components/LoadingDots';
import PaymentInfo from './PaymentInfo';
import RefundInfo from './RefundInfo';
import { IMollieApiPayment, IMollieOrderConfig, ITranslations } from '../../../../globals';

interface IProps {
  // Redux
  config?: IMollieOrderConfig;
  translations?: ITranslations;
  payment?: IMollieApiPayment;
  dispatchUpdatePayment?: Function;
}

function RefundPanel({ payment, config }: IProps) {
  if (Object.keys(config).length <= 0) {
    return null;
  }
  const { moduleDir, legacy } = config;

  if (legacy) {
    return (
      <fieldset style={{ marginTop: '14px' }}>
        <legend className="panel-heading">
          <img
            src={`${moduleDir}views/img/logo_small.png`}
            width="32"
            height="32"
            style={{ height: '16px', width: '16px', opacity: 0.8 }}
          />
          <span>Mollie</span>&nbsp;
        </legend>
        {!payment && <LoadingDots/>}
        {!!payment && payment.status && (
          <>
            <PaymentInfo/>
            <RefundInfo/>
          </>
        )}
      </fieldset>
    );
  }

  return (
    <div className="panel">
      <div className="panel-heading">
        <img
          src={`${moduleDir}views/img/mollie_panel_icon.png`}
          width="32"
          height="32"
          style={{ height: '16px', width: '16px', opacity: 0.8 }}
        /> <span>Mollie</span>&nbsp;
      </div>
      {!payment && <LoadingDots/>}
      {!!payment && payment.status && (
        <div className="panel-body row">
          <PaymentInfo/>
          <RefundInfo/>
        </div>
      )}
    </div>
  );
}

export default connect<{}, {}, IProps>(
  (state: IMollieOrderState): Partial<IProps> => ({
    translations: state.translations,
    config: state.config,
    payment: state.payment,
  }),
  (dispatch: Dispatch): Partial<IProps> => ({
    dispatchUpdatePayment(payment: IMollieApiPayment) {
      dispatch(updatePayment(payment));
    }
  })
)
(RefundPanel);

