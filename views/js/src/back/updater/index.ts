/**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 * @link        https://github.com/mollie/PrestaShop
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 * @codingStandardsIgnoreStart
 */
import xss from 'xss';
import { get } from 'lodash';

import axios from '@shared/axios';
import { ITranslations } from '@shared/globals';

const showError = async (message: string): Promise<void> => {
  const [
    { default: swal },
  ] = await Promise.all([
      import(/* webpackPrefetch: true, webpackChunkName: "sweetalert" */ 'sweetalert'),
  ]);
  swal({
    icon: 'error',
    title: get(document, 'documentElement.lang', 'en') === 'nl' ? 'Fout' : 'Error',
    text: xss(message),
  }).then();
};

const handleClick = async (config: any, translations: ITranslations): Promise<void> => {
  const steps = [
    {
      action: 'downloadUpdate',
      defaultError: translations.unableToConnect,
    },
    {
      action: 'downloadUpdate',
      defaultError: translations.unableToUnzip,
    },
    {
      action: 'downloadUpdate',
      defaultError: translations.unableToConnect,
    },
  ];

  for (let step of steps) {
    try {
      const { data } = await axios.get(`${config.endpoint}&action=${step.action}`);
      if (!get(data, 'success')) {
        showError(get(data, 'message', step.defaultError)).then();
      }
    } catch (e) {
      console.error(e);
      showError(step.defaultError).then();
    }
  }

  import(/* webpackPrefetch: true, webpackChunkName: "sweetalert" */ 'sweetalert').then(({ default: swal }) => {
    swal({
      icon: 'success',
      text: translations.updated
    }).then();
  });
};

export default (button: HTMLElement, config: any, translations: ITranslations) => {
  button.onclick = () => handleClick(config, translations);
};

