/**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 * @link        https://github.com/mollie/PrestaShop
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 * @codingStandardsIgnoreStart
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
