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
import axios from 'axios';
import PaymentMethods from './PaymentMethods';
import LoadingDots from '../../misc/components/LoadingDots';

interface IProps {
  config: IMollieMethodConfig,
  translations: ITranslations,
  target: string,
}

interface IState {
  methods: Array<IMolliePaymentMethodItem>,
  reset: boolean,
}

class PaymentMethodConfig extends Component<IProps> {
  readonly state: IState = {
    methods: undefined,
    reset: true,
  };

  componentDidMount() {
    this.init();
  }

  init = () => {
    const { config: { ajaxEndpoint } } = this.props;
    setTimeout(async () => {
      try {
        const { data: { methods } = { methods: null } } = await axios.post(ajaxEndpoint, {
          resource: 'orders',
          action: 'retrieve',
        });

        this.setState(() => ({ methods, reset: false }), () => this.setState(() => ({ reset: true })));
      } catch (e) {
        console.error(e);

        this.setState(() => ({ methods: null, reset: false }), () => this.setState(() => ({ reset: true })));

      }
    }, 0);
  };

  render() {
    const { target, translations, config } = this.props;
    const { reset, methods } = this.state;

    if (typeof methods === 'undefined') {
      return <LoadingDots/>;
    }

    return (
      <Fragment>
        {reset && <PaymentMethods methods={methods} translations={translations} target={target} config={config} retry={this.init}/>}
      </Fragment>
    );
  }
}

export default PaymentMethodConfig;
