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
import React, { ReactElement, useEffect, useState } from 'react';
import { isEmpty } from 'lodash';

import PaymentMethods from '@methodconfig/components/PaymentMethods';
import PaymentMethodsError from '@methodconfig/components/PaymentMethodsError';
import axios from '@shared/axios';
import { IMollieMethodConfig, IMolliePaymentMethodItem, ITranslations } from '@shared/globals';
import LoadingDots from '@shared/components/LoadingDots';

interface IProps {
  config: IMollieMethodConfig;
  translations: ITranslations;
  target: string;
}

export default function PaymentMethodConfig(props: IProps): ReactElement<{}> {
  const [methods, setMethods] = useState<Array<IMolliePaymentMethodItem>>(undefined);
  const [message, setMessage] = useState<string>(undefined);

  async function _init(): Promise<void> {
    try {
      const { config: { ajaxEndpoint } } = props;
      const { data: { methods: newMethods, message: newMessage } = { methods: null, message: '' } } = await axios.post(ajaxEndpoint, {
        resource: 'orders',
        action: 'retrieve',
      });

      setMethods(newMethods);
      setMessage(newMessage);
    } catch (e) {
      setMethods(null);
      setMessage((e instanceof Error && typeof e.message !== 'undefined') ? e.message : 'Check the browser console for errors');
    }
  }

  useEffect(() => {
    _init().then();
  }, []);

  const { target, translations, config } = props;

  if (typeof methods === 'undefined') {
    return <LoadingDots/>;
  }

  if (methods === null || !Array.isArray(methods) || (Array.isArray(methods) && isEmpty(methods))) {
    return <PaymentMethodsError message={message} translations={translations} config={config} retry={_init}/>;
  }

  return (
    <PaymentMethods methods={methods} translations={translations} target={target} config={config}/>
  );
}
