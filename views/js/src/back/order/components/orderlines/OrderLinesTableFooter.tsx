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
import { connect } from 'react-redux';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { faCircleNotch, faTimes, faTruck, faUndoAlt } from '@fortawesome/free-solid-svg-icons';
import { compact } from 'lodash';

import { IMollieApiOrder, IMollieOrderConfig, ITranslations } from '../../../../globals';

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

function OrderLinesTableFooter(props: IProps) {
  function getCancelable() {
    for (let line of Object.values(props.order.lines.filter(line => line.type !== 'discount'))) {
      if (line.cancelableQuantity >= 1) {
        return true;
      }
    }

    return false;
  }

  function getShippable() {
    for (let line of Object.values(props.order.lines.filter(line => line.type !== 'discount'))) {
      if (line.shippableQuantity >= 1) {
        return true;
      }
    }

    return false;
  }

  function getRefundable() {
    for (let line of Object.values(props.order.lines.filter(line => line.type !== 'discount'))) {
      if (line.refundableQuantity >= 1) {
        return true;
      }
    }

    return false;
  }

  const { translations, loading, order, ship, cancel, refund, config: { legacy } } = props;

  return (
    <tfoot>
      <tr>
        <td colSpan={10}>
          <div className="btn-group" role="group">
            <button
              type="button"
              onClick={() => ship(compact(order.lines.filter(line => line.type !== 'discount')))}
              className="btn btn-primary"
              disabled={loading || !getShippable()}
              style={{
                cursor: (loading || !getShippable()) ? 'not-allowed' : 'pointer',
                opacity: (loading || !getShippable()) ? 0.8 : 1
              }}
            >
              {legacy && (
                <img
                  src="../img/admin/delivery.gif"
                  style={{
                    filter: (loading || !getShippable()) ? 'grayscale(100%)' : null,
                    WebkitFilter: (loading || !getShippable()) ? 'grayscale(100%)' : null,
                  }}
                />
              )}
              {!legacy && <FontAwesomeIcon icon={loading ? faCircleNotch : faTruck} spin={loading}/>} {translations.shipAll}
            </button>
            <button
              type="button"
              onClick={() => refund(compact(order.lines.filter(line => line.type !== 'discount')))}
              className="btn btn-default"
              disabled={loading || !getRefundable()}
              style={{
                cursor: (loading || !getRefundable()) ? 'not-allowed' : 'pointer',
                opacity: (loading || !getRefundable()) ? 0.8 : 1
              }}
            >
              {legacy && (
                <img
                  src="../img/admin/money.gif"
                  style={{
                    filter: (loading || !getRefundable()) ? 'grayscale(100%)' : null,
                    WebkitFilter: (loading || !getRefundable()) ? 'grayscale(100%)' : null,
                  }}
                />
              )}
              {!legacy && <FontAwesomeIcon icon={loading ? faCircleNotch : faUndoAlt} spin={loading}/>} {translations.refundAll}
            </button>
            <button
              type="button"
              onClick={() => cancel(compact(order.lines.filter(line => line.type !== 'discount')))}
              className="btn btn-default"
              disabled={loading || !getCancelable()}
              style={{
                cursor: (loading || !getCancelable()) ? 'not-allowed' : 'pointer',
                opacity: (loading || !getCancelable()) ? 0.8 : 1
              }}
            >
              {legacy && (
                <img
                  src="../img/admin/disabled.gif"
                  style={{
                    filter: (loading || !getCancelable()) ? 'grayscale(100%)' : null,
                    WebkitFilter: (loading || !getCancelable()) ? 'grayscale(100%)' : null,
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

export default connect<{}, {}, IProps>(
  (state: IMollieOrderState): Partial<IProps> => ({
    translations: state.translations,
    order: state.order,
    config: state.config,
  })
)(OrderLinesTableFooter);
