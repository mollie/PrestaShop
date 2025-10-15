/**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 * @link        https://github.com/mollie/PrestaShop
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 * @codingStandardsIgnoreStart
 */
import React, { ReactElement, useCallback } from 'react';
import cx from 'classnames';
import { faRedoAlt } from '@fortawesome/free-solid-svg-icons';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import styled from 'styled-components';

import { IMollieCarrierConfig, ITranslations } from '@shared/globals';
import { useMappedState } from 'redux-react-hook';

interface IProps {
  retry: Function;
  message?: string;
}

const Code = styled.code`
  font-size: 14px!important;
` as any;

export default function ConfigCarrierError({ retry, message }: IProps): ReactElement<{}> {
  const { translations, config: { legacy } }: Partial<IMollieOrderState> = useCallback(useMappedState((state: IMollieCarriersState): any => ({
    translations: state.translations,
    config: state.config,
  })), []);

  return (
    <div
      className={cx({
        'alert': !legacy,
        'alert-danger': !legacy,
        'error': legacy,
      })}
    >
      {translations.unableToLoadCarriers}&nbsp;
      {message && <><br/><br/>{translations.error}: <Code>{message}</Code><br/><br/></>}
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
