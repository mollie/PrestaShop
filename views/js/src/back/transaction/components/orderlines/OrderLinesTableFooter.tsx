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
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { faCircleNotch, faTimes, faTruck, faUndoAlt } from '@fortawesome/free-solid-svg-icons';
import { compact, get } from 'lodash';

import { IMollieApiOrder, IMollieOrderConfig, ITranslations } from '@shared/globals';
import { useMappedState } from 'redux-react-hook';
import { formatCurrency } from "@shared/tools";

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
  const { translations, order, currencies, config: { legacy } }: Partial<IMollieOrderState> = useMappedState((state: IMollieOrderState): any => ({
    translations: state.translations,
    currencies: state.currencies,
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
      if (line.refundableQuantity >= 1 && parseFloat(order.availableRefundAmount.value) > 0.0) {
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
