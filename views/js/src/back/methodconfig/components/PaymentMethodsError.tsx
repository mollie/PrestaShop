/**
 * Copyright (c) 2012-2020, Mollie B.V.
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
