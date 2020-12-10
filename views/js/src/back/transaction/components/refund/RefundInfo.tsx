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
import React, { ReactElement, useCallback } from 'react';
import styled from 'styled-components';

import RefundHistory from '@transaction/components/refund/RefundHistory';
import RefundForm from '@transaction/components/refund/RefundForm';
import { useMappedState } from 'redux-react-hook';

const Div = styled.div`
@media only screen and (min-width: 992px) {
  margin-left: 5px!important;
  margin-right: -5px!important;
}
` as any;

export default function RefundInfo(): ReactElement<{}> {
  const { translations, config: { legacy }, payment }: Partial<IMollieOrderState> = useMappedState( (state: IMollieOrderState): any => ({
    payment: state.payment,
    translations: state.translations,
    config: state.config,
  }));

  if (legacy) {
    return (
        <>
          <h3>{translations.refunds}</h3>
          {payment.amountRefunded && <RefundHistory/>}
          {payment.amountRefunded && <RefundForm/>}
          {!payment.amountRefunded && <div className="warn">{translations.refundsAreCurrentlyUnavailable}</div>}
        </>
    );
  }

  return (
      <Div className="col-md-9">
        <div className="panel card">
          <div className="panel-heading card-header">{translations.refunds}</div>
          <div className="card-body">
            {payment.amountRefunded && <RefundHistory/>}
            {payment.amountRefunded && <RefundForm/>}
            {!payment.amountRefunded &&
            <div className="alert alert-warning">{translations.refundsAreCurrentlyUnavailable}</div>}
          </div>
        </div>
      </Div>
  );
}