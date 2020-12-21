/**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 * @link        https://github.com/mollie/PrestaShop
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 * @codingStandardsIgnoreStart
 */
import React, { ReactElement, useCallback, useState } from 'react';
import { render } from 'react-dom';
import cx from 'classnames';
import xss from 'xss';
import { get, isEmpty } from 'lodash';
import styled from 'styled-components';
import { useDispatch, useMappedState } from 'redux-react-hook';
import { SweetAlert } from 'sweetalert/typings/core';

import OrderLinesTableHeader from '@transaction/components/orderlines/OrderLinesTableHeader';
import OrderLinesTableFooter from '@transaction/components/orderlines//OrderLinesTableFooter';
import OrderLinesEditor from '@transaction/components/orderlines//OrderLinesEditor';
import ShipmentTrackingEditor from '@transaction/components/orderlines//ShipmentTrackingEditor';
import { cancelOrder, refundOrder, shipOrder } from '@transaction/misc/ajax';
import {updateOrder, updateWarning} from '@transaction/store/actions';
import OrderLinesTableActions from '@transaction/components/orderlines//OrderLinesTableActions';
import { formatCurrency } from '@shared/tools';
import { IMollieApiOrder, IMollieOrderLine, IMollieTracking, } from '@shared/globals';

const TableContainer = styled.div`
@media (min-width: 1280px) {
  overflow: ${({ config: { legacy } }: Partial<IMollieOrderState>) => legacy ? 'inherit' : 'visible!important'};
}
` as any;

export default function OrderLinesTable(): ReactElement<{}> {
  const [loading, setLoading] = useState<boolean>(false);
  const { translations, order, currencies, config: { legacy }, config, viewportWidth }: Partial<IMollieOrderState> = useMappedState((state: IMollieOrderState): any => ({
    order: state.order,
    currencies: state.currencies,
    translations: state.translations,
    config: state.config,
    viewportWidth: state.viewportWidth,
  }));
  const dispatch = useDispatch();

  async function _ship(origLines: Array<IMollieOrderLine>): Promise<void> {
    let lines = null;

    const reviewWrapper = document.createElement('DIV');
    render(<OrderLinesEditor lineType="shippable" translations={translations} lines={origLines} edited={newLines => lines = newLines}/>, reviewWrapper);
    let el: any = reviewWrapper.firstChild;

    const { default: swal } = await import(/* webpackPrefetch: true, webpackChunkName: "sweetalert" */ 'sweetalert') as never as { default: SweetAlert };
    let input = await swal({
      title: xss(translations.reviewShipment),
      text: xss(translations.reviewShipmentProducts),
      buttons: {
        cancel: {
          text: xss(translations.cancel),
          visible: true,
        },
        confirm: {
          text: xss(translations.OK),
        }
      },
      closeOnClickOutside: false,
      content: el,
    });
    if (input) {
      let tracking: IMollieTracking = {
        carrier: '',
        code: '',
        url: '',
      };
      const checkSwalButton = async (): Promise<void> => {
        const elem: HTMLInputElement = document.querySelector('.swal-button.swal-button--confirm');

        elem.disabled = tracking && (isEmpty(tracking.code.replace(/\s+/, '')) || isEmpty(tracking.carrier.replace(/\s+/, '')));
      };

      const updateTracking = (newTracking: IMollieTracking): void => {
        tracking = newTracking;
        checkSwalButton().then();
      };

      let trackingWrapper = document.createElement('DIV');
      trackingWrapper.innerHTML = '';
      render(<ShipmentTrackingEditor checkButtons={checkSwalButton} config={config} translations={translations} edited={newTracking => updateTracking(newTracking)}/>, trackingWrapper);
      el = trackingWrapper.firstChild;
      [input] = await Promise.all([swal({
        title: xss(translations.trackingDetails),
        text: xss(translations.addTrackingInfo),
        buttons: {
          cancel: {
            text: xss(translations.cancel),
            visible: true,
          },
          confirm: {
            text: xss(translations.shipProducts),
          },
        },
        closeOnClickOutside: false,
        content: el,
      }), checkSwalButton()]);
      if (input) {
        try {
          setLoading(true);
          const { success, order: newOrder } = await shipOrder(order.id, lines, tracking);
          if (success) {
            dispatch(updateOrder(newOrder));
            dispatch(updateWarning('shipped'));
          } else {
            import(/* webpackPrefetch: true, webpackChunkName: "sweetalert" */ 'sweetalert').then(({ default: swal }) => {
              swal({
                icon: 'error',
                title: xss(translations.anErrorOccurred),
                text: xss(translations.unableToShip),
              }).then();
            });
          }
        } catch (e) {
          if (typeof e === 'string') {
            import(/* webpackPrefetch: true, webpackChunkName: "sweetalert" */ 'sweetalert').then(({ default: swal }) => {
              swal({
                icon: 'error',
                title: xss(translations.anErrorOccurred),
                text: xss(e),
              }).then();
            });
          }
          console.error(e);
        } finally {
          setLoading(false);
        }
      }
    }
  }

  async function _refund(origLines: Array<IMollieOrderLine>): Promise<void> {
    let lines = null;
    const reviewWrapper = document.createElement('DIV');
    const filteredLines = origLines.filter((line) =>  !parseFloat(line.amountRefunded.value) > 0)
    render(<OrderLinesEditor lineType="refundable" translations={translations} lines={filteredLines} edited={newLines => lines = newLines}/>, reviewWrapper);
    let el: any = reviewWrapper.firstChild;
    const { default: swal }= await import(/* webpackPrefetch: true, webpackChunkName: "sweetalert" */ 'sweetalert') as never as { default: SweetAlert };
    let input = await swal({
      title: xss(translations.reviewShipment),
      text: xss(translations.reviewShipmentProducts),
      buttons: {
        cancel: {
          text: xss(translations.cancel),
          visible: true,
        },
        confirm: {
          text: xss(translations.OK),
        },
      },
      closeOnClickOutside: false,
      content: el,
    });
    if (input) {
      try {
        setLoading(true);
        const { success, order: newOrder } = await refundOrder(order, lines);
        if (success) {
          dispatch(updateWarning('refunded'));
          dispatch(updateOrder(newOrder));
        } else {
          import(/* webpackPrefetch: true, webpackChunkName: "sweetalert" */ 'sweetalert').then(({ default: swal }) => {
            swal({
              icon: 'error',
              title: xss(translations.anErrorOccurred),
              text: xss(translations.unableToRefund),
            }).then();
          });
        }
      } catch (e) {
        if (typeof e === 'string') {
          import(/* webpackPrefetch: true, webpackChunkName: "sweetalert" */ 'sweetalert').then(({ default: swal }) => {
            swal({
              icon: 'error',
              title: xss(translations.anErrorOccurred),
              text: xss(e),
            }).then();
          });
        }
        console.error(e);
      } finally {
        setLoading(false);
      }
    }
  }

  async function _cancel(origLines: Array<IMollieOrderLine>): Promise<void> {
    let lines = null;
    const reviewWrapper = document.createElement('DIV');
    render(<OrderLinesEditor lineType="cancelable" translations={translations} lines={origLines} edited={newLines => lines = newLines}/>, reviewWrapper);
    let el: any = reviewWrapper.firstChild;

    const { default: swal } = await import(/* webpackPrefetch: true, webpackChunkName: "sweetalert" */ 'sweetalert') as never as { default: SweetAlert };
    let input = await swal({
      title: xss(translations.reviewShipment),
      text: xss(translations.reviewShipmentProducts),
      buttons: {
        cancel: {
          text: xss(translations.cancel),
          visible: true,
        },
        confirm: {
          text: xss(translations.OK),
        },
      },
      closeOnClickOutside: false,
      content: el,
    });
    if (input) {
      try {
        setLoading(true);
        const { success, order: newOrder } = await cancelOrder(order.id, lines);
        if (success) {
          dispatch(updateOrder(newOrder));
          dispatch(updateWarning('canceled'));
        } else {
          swal({
            icon: 'error',
            title: xss(translations.anErrorOccurred),
            text: xss(translations.unableToShip),
          }).then();
        }
      } catch (e) {
        if (typeof e === 'string') {
          swal({
            icon: 'error',
            title: xss(translations.anErrorOccurred),
            text: xss(e),
          }).then();
        }
        console.error(e);
      } finally {
        setLoading(false);
      }
    }
  }

  return (
    <TableContainer
      className={cx({
        'table-responsive': !legacy,
      })}
      order={order}
      currencies={currencies}
      translations={translations}
      config={config}
      viewportWidth={viewportWidth}
    >
      <table className={cx({
        'table': true,
      })}>
        <OrderLinesTableHeader/>
        <tbody>
          {order.lines.map((line: IMollieOrderLine) => (
            <tr key={line.id} style={{ marginBottom: '100px' }}>
              <td><strong>{line.quantity}x</strong> {line.name}</td>
              <td>{line.status}</td>
              {viewportWidth < 1390 && <td>{line.quantityShipped} / {line.quantityCanceled} / {line.quantityRefunded}</td>}
              {viewportWidth >= 1390 && <td>{line.quantityShipped}</td>}
              {viewportWidth >= 1390 && <td>{line.quantityCanceled}</td>}
              {viewportWidth >= 1390 && <td>{line.quantityRefunded}</td>}
              <td>{formatCurrency(parseFloat(line.unitPrice.value), get(currencies, line.unitPrice.currency))}</td>
              <td>{formatCurrency(parseFloat(line.vatAmount.value), get(currencies, line.vatAmount.currency))} ({line.vatRate}%)</td>
              <td>{formatCurrency(parseFloat(line.totalAmount.value), get(currencies, line.totalAmount.currency))}</td>
              <td className={cx({
                'actions': !legacy,
              })}>
                <OrderLinesTableActions
                  loading={loading}
                  line={line}
                  availableRefundAmount={order.availableRefundAmount}
                  refundLine={_refund}
                  shipLine={_ship}
                  cancelLine={_cancel}
                />
              </td>
            </tr>
          ))}
        </tbody>
        <OrderLinesTableFooter loading={loading} ship={_ship} refund={_refund} cancel={_cancel}/>
      </table>
    </TableContainer>
  );
}
