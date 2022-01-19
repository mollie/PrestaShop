/**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 * @link        https://github.com/mollie/PrestaShop
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 * @codingStandardsIgnoreStart
 */
import React, { ReactElement } from 'react';
import cx from 'classnames';
import { faRedoAlt } from '@fortawesome/free-solid-svg-icons';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import styled from 'styled-components';

import { IMollieMethodConfig, ITranslations } from '@shared/globals';

interface IProps {
  translations: ITranslations;
  config: IMollieMethodConfig;
  message: string;
  retry: Function;
}

const Code = styled.code`
  font-size: 14px!important;
` as any;

export default function PaymentMethodsError({ translations, config: { legacy }, retry, message }: IProps): ReactElement<{}> {
  return (
    <div
      className={cx({
        'alert': !legacy,
        'alert-danger': !legacy,
        'error': legacy,
      })}
    >
      {translations.unableToLoadMethods}&nbsp;
      {message && <><br/><br/><span>{translations.error}: <Code>{message}</Code></span><br/><br/></>}
      <button
        className={cx({
          'btn': !legacy,
          'btn-danger': !legacy,
          'button': legacy,
        })}
        onClick={(e) => {
          e.preventDefault();
          retry();
        }}
      >
        {!legacy && <FontAwesomeIcon icon={faRedoAlt}/>}&nbsp;{translations.retry}?
      </button>
    </div>
  );
}
