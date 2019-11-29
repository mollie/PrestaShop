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
import React, { ReactElement, useCallback } from 'react';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { faCircleNotch, faTimes, faTruck, faUndoAlt } from '@fortawesome/free-solid-svg-icons';
import { compact } from 'lodash';

import { IMollieApiOrder, IMollieOrderConfig, ITranslations } from '@shared/globals';
import { useMappedState } from 'redux-react-hook';

interface IProps {
  loading: boolean;
  ship: Function;
  refund: Function;
  cancel: Function;

  // Redux
  order?: IMollieApiOrder;
  translations?: ITranslations;
  config?: IMollieOrderConfig;
}

export default function OrderLinesTableFooter({ loading, ship, cancel, refund }: IProps): ReactElement<{}> {
  const { translations, order, config: { legacy } }: Partial<IMollieOrderState> = useMappedState((state: IMollieOrderState): any => ({
    translations: state.translations,
    order: state.order,
    config: state.config,
  }));

  function isCancelable(): boolean {
    for (let line of Object.values(order.lines.filter(line => line.type !== 'discount'))) {
      if (line.cancelableQuantity >= 1) {
        return true;
      }
    }

    return false;
  }

  function isShippable(): boolean {
    for (let line of Object.values(order.lines.filter(line => line.type !== 'discount'))) {
      if (line.shippableQuantity >= 1) {
        return true;
      }
    }

    return false;
  }

  function isRefundable(): boolean {
    for (let line of Object.values(order.lines.filter(line => line.type !== 'discount'))) {
      if (line.refundableQuantity >= 1) {
        return true;
      }
    }

    return false;
  }

  return (
    <tfoot>
      <tr>
        <td colSpan={10}>
          <div className="btn-group" role="group">
            <button
              type="button"
              onClick={() => ship(compact(order.lines.filter(line => line.type !== 'discount')))}
              className="btn btn-primary"
              disabled={loading || !isShippable()}
              style={{
                cursor: (loading || !isShippable()) ? 'not-allowed' : 'pointer',
                opacity: (loading || !isShippable()) ? 0.8 : 1
              }}
            >
              {legacy && (
                <img
                  src="../img/admin/delivery.gif"
                  alt=""
                  style={{
                    filter: (loading || !isShippable()) ? 'grayscale(100%)' : null,
                    WebkitFilter: (loading || !isShippable()) ? 'grayscale(100%)' : null,
                  }}
                />
              )}
              {!legacy && <FontAwesomeIcon icon={loading ? faCircleNotch : faTruck} spin={loading}/>} {translations.shipAll}
            </button>
            <button
              type="button"
              onClick={() => refund(compact(order.lines.filter(line => line.type !== 'discount')))}
              className="btn btn-default"
              disabled={loading || !isRefundable()}
              style={{
                cursor: (loading || !isRefundable()) ? 'not-allowed' : 'pointer',
                opacity: (loading || !isRefundable()) ? 0.8 : 1
              }}
            >
              {legacy && (
                <img
                  src="../img/admin/money.gif"
                  alt=""
                  style={{
                    filter: (loading || !isRefundable()) ? 'grayscale(100%)' : null,
                    WebkitFilter: (loading || !isRefundable()) ? 'grayscale(100%)' : null,
                  }}
                />
              )}
              {!legacy && <FontAwesomeIcon icon={loading ? faCircleNotch : faUndoAlt} spin={loading}/>} {translations.refundAll}
            </button>
            <button
              type="button"
              onClick={() => cancel(compact(order.lines.filter(line => line.type !== 'discount')))}
              className="btn btn-default"
              disabled={loading || !isCancelable()}
              style={{
                cursor: (loading || !isCancelable()) ? 'not-allowed' : 'pointer',
                opacity: (loading || !isCancelable()) ? 0.8 : 1
              }}
            >
              {legacy && (
                <img
                  src="../img/admin/disabled.gif"
                  alt=""
                  style={{
                    filter: (loading || !isCancelable()) ? 'grayscale(100%)' : null,
                    WebkitFilter: (loading || !isCancelable()) ? 'grayscale(100%)' : null,
                  }}
                />
              )}
              {!legacy && <FontAwesomeIcon icon={loading ? faCircleNotch : faTimes} spin={loading}/>} {translations.cancelAll}
            </button>
          </div>
        </td>
      </tr>
    </tfoot>
  );
}
