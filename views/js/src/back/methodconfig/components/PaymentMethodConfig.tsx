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
import axios from '../../misc/axios';
import PaymentMethods from './PaymentMethods';
import LoadingDots from '../../misc/components/LoadingDots';
import _ from 'lodash';
import PaymentMethodsError from './PaymentMethodsError';

interface IProps {
  config: IMollieMethodConfig,
  translations: ITranslations,
  target: string,
}

interface IState {
  methods: Array<IMolliePaymentMethodItem>,
  message: string,
}

class PaymentMethodConfig extends Component<IProps> {
  readonly state: IState = {
    methods: undefined,
    message: '',
  };

  componentDidMount() {
    setTimeout(this.init, 0);
  }

  init = async (): Promise<void> => {
    try {
      this.setState({ methods: undefined });
      const { config: { ajaxEndpoint } } = this.props;
      const { data: { methods, message } = { methods: null, message: '' } } = await axios.post(ajaxEndpoint, {
        resource: 'orders',
        action: 'retrieve',
      });

      this.setState({ methods, message });
    } catch (e) {
      this.setState({
        methods: null,
        message: (e instanceof Error && typeof e.message !== 'undefined') ? e.message : 'Check the browser console for errors',
      });
    }
  };

  render() {
    const { target, translations, config } = this.props;
    const { methods, message } = this.state;

    if (typeof methods === 'undefined') {
      return <LoadingDots/>;
    }

    if (methods === null || !_.isArray(methods) || _.isArray(methods) && _.isEmpty(methods)) {
      return <PaymentMethodsError message={message} translations={translations} config={config} retry={this.init}/>;
    }

    return (
      <PaymentMethods methods={methods} translations={translations} target={target} config={config}/>
    );
  }
}

export default PaymentMethodConfig;
