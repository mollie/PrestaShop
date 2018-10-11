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
import { connect } from 'react-redux';
import axios from 'axios';

import swal from 'sweetalert';
import xss from 'xss';
import { Dispatch } from 'redux';
import { updateStatus } from '../store/actions';
import RefundButton from './RefundButton';
import PartialRefundButton from './PartialRefundButton';

interface IProps {
  // Redux
  config?: IMollieOrderConfig,
  translations?: ITranslations,

  dispatchUpdateStatus?: Function,
}

interface IState {
  loading: boolean,
}

declare let window: any;

class RefundForm extends Component<IProps> {
  state: IState = {
    loading: false,
  };

  refund = async (event: any) => {
    event.preventDefault();

    const {
      dispatchUpdateStatus,
      translations,
      config: { ajaxEndpoint, orderId, transactionId },
    } = this.props;

    // @ts-ignore
    const input = await swal({
      dangerMode: true,
      icon: 'warning',
      title: xss(translations.areYouSure),
      text: xss(translations.areYouSureYouWantToRefund),
      buttons: {
        cancel: xss(translations.cancel),
        confirm: xss(translations.refund),
      },
    });
    if (input) {
      try {
        const { data: { success } } = await axios.post(ajaxEndpoint, {
          resource: 'payments',
          action: 'refund',
          orderId,
          transactionId,
        });
        if (success) {
          dispatchUpdateStatus('success');
        }
      } catch (e) {
      }
    }
  };

  render() {
    const { loading } = this.state;
    const { translations } = this.props;
    return (
      <Fragment>
        <h4>{translations.refund}</h4>
        <div className="well well-sm">
          <div className="form-inline">
            <div className="form-group">
              <RefundButton loading={loading}/>
            </div>
            <div className="form-group" style={{ marginLeft: '10px' }}>
              <div className="input-group" style={{ minWidth: '400px' }}>
                <div className="input-group-addon">
                  {translations.remaining}:
                </div>
                <input type="hidden" value="1398"/>
                <input type="text" className="form-control" placeholder="$59.59"/>
                <PartialRefundButton loading={loading}/>
              </div>
            </div>
          </div>
        </div>
      </Fragment>
    );
  }
}

export default connect<{}, {}, IProps>(
  (state: IMollieOrderState): Partial<IProps> => ({
    translations: state.translations,
    config: state.config,
  }),
  (state: IMollieOrderState, dispatch: Dispatch): Partial<IProps> => ({
    dispatchUpdateStatus(status: string) {
      dispatch(updateStatus(status));
    }
  })
)
(RefundForm);

