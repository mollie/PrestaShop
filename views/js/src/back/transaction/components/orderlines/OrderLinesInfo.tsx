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
import React, {ReactElement, useCallback, useState} from 'react';
import styled from 'styled-components';

import OrderLinesTable from '@transaction/components/orderlines/OrderLinesTable';
import EmptyOrderLinesTable from '@transaction/components/orderlines/EmptyOrderLinesTable';
import { IMollieApiOrder, IMollieOrderConfig, ITranslations } from '@shared/globals';
import { useMappedState } from 'redux-react-hook';

interface IProps {
  // Redux
  translations?: ITranslations;
  order?: IMollieApiOrder;
  config?: IMollieOrderConfig;
}

const Div = styled.div`
@media only screen and (min-width: 992px) {
  margin-left: 5px!important;
  margin-right: -5px!important;
}
` as any;

export default function OrderLinesInfo(): ReactElement<{}> {
  const { translations, order, config: { legacy } }: IProps = useMappedState((state: IMollieOrderState): any => ({
    translations: state.translations,
    order: state.order,
    config: state.config,
  }));

  if (legacy) {
    return (
      <>
        {legacy && <h3>{translations.products}</h3>}
        {!legacy && <h4>{translations.products}</h4>}
        {!order || (!order.lines.length && <EmptyOrderLinesTable/>)}
        {!!order && !!order.lines.length && <OrderLinesTable/>}
      </>
    );
  }

  return (
    <Div className="col-md-9 panel">
      <div className="panel-heading">{translations.products}</div>
      {!order || (!order.lines.length && <EmptyOrderLinesTable/>)}
      {!!order && !!order.lines.length && <OrderLinesTable/>}
    </Div>
  );
}
