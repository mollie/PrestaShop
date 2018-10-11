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
import { connect, Provider } from 'react-redux';

import RefundForm from './RefundForm';
import RefundFail from './RefundFail';
import RefundSuccess from './RefundSuccess';
import store from './store';
import { Store } from 'redux';

interface IProps {
  store: Store,

  // Redux
  config?: IMollieOrderConfig,
  translations?: ITranslations,
  status?: string,
}

class RefundPanel extends Component<IProps> {
  render() {
    const { status, config } = this.props;
    if (Object.keys(config).length <= 0) {
      return null;
    }
    const { moduleDir } = config;

    let content;
    switch (status) {
      case 'form':
        content = <RefundForm message={'test'}/>;
        break;
      case 'fail':
        content = <RefundFail failMessage={'test'} failDetails={'tast'}/>;
        break;
      case 'success':
        content = <RefundSuccess successMessage={'test'} successDetails={'tast'}/>;
        break;
    }

    return (
      <Provider store={store}>
        <div className="panel">
          <div className="panel-heading">
            <img
              src={`${moduleDir}views/img/mollie_panel_icon.png`}
              height="32"
              width="32"
              style={{
                height: '14px',
                width: '14px',
              }}
            /> Mollie
          </div>
          <div className="mollie_refund_button_box">
            {content}
          </div>
        </div>
      </Provider>
    );
  }
}

export default connect<{}, {}, IProps>(
  (state: IMollieOrderState): Partial<IProps> => ({
    status: state.status,
    translations: state.translations,
    config: state.config,
  })
)
(RefundPanel);

