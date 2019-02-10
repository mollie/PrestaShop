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
import React, { useState } from 'react';
import { render } from 'react-dom';
import { connect } from 'react-redux';
import classnames from 'classnames';
import swal from 'sweetalert';
import xss from 'xss';
import { Dispatch } from 'redux';
import { get, isEmpty } from 'lodash';
import styled from 'styled-components';

import OrderLinesTableHeader from '@transaction/components/orderlines/OrderLinesTableHeader';
import OrderLinesTableFooter from '@transaction/components/orderlines//OrderLinesTableFooter';
import OrderLinesEditor from '@transaction/components/orderlines//OrderLinesEditor';
import ShipmentTrackingEditor from '@transaction/components/orderlines//ShipmentTrackingEditor';
import { cancelOrder, refundOrder, shipOrder } from '@transaction/misc/ajax';
import { updateOrder } from '@transaction/store/actions';
import OrderLinesTableActions from '@transaction/components/orderlines//OrderLinesTableActions';
import { formatCurrency } from '@shared/tools';
import {
  ICurrencies,
  IMollieApiOrder,
  IMollieOrderConfig,
  IMollieOrderLine,
  IMollieTracking,
  ITranslations,
} from '@shared/globals';

interface IProps {
  // Redux
  order?: IMollieApiOrder;
  currencies?: ICurrencies;
  translations?: ITranslations;
  config?: IMollieOrderConfig;
  viewportWidth?: number;

  dispatchUpdateOrder?: Function;
}

const TableContainer = styled.div`
@media (min-width: 1280px) {
  overflow: ${({ config: { legacy } }: IProps) => legacy ? 'inherit' : 'visible!important'};
}
` as any;

function OrderLinesTable(props: IProps) {
  const [loading, setLoading] = useState<boolean>(false);

  async function ship(origLines: Array<IMollieOrderLine>) {
    let lines = null;
    const { translations, order, dispatchUpdateOrder, config } = props;

    const reviewWrapper = document.createElement('DIV');
    render(<OrderLinesEditor lineType="shippable" translations={translations} lines={origLines} edited={newLines => lines = newLines}/>, reviewWrapper);
    let el = reviewWrapper.firstChild;

    // @ts-ignore
    let input = await swal({
      title: xss(translations.reviewShipment),
      text: xss(translations.reviewShipmentProducts),
      buttons: [xss(translations.cancel), xss(translations.OK)],
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

      const updateTracking = (newTracking: IMollieTracking) => {
        tracking = newTracking;
        checkSwalButton().then();
      };

      let trackingWrapper = document.createElement('DIV');
      trackingWrapper.innerHTML = '';
      render(<ShipmentTrackingEditor checkButtons={checkSwalButton} config={config} translations={translations} edited={newTracking => updateTracking(newTracking)}/>, trackingWrapper);
      el = trackingWrapper.firstChild;
      // @ts-ignore
      [input] = await Promise.all([swal({
        title: xss(translations.trackingDetails),
        text: xss(translations.addTrackingInfo),
        buttons: [xss(translations.cancel), xss(translations.shipProducts)],
        closeOnClickOutside: false,
        content: el,
      }), checkSwalButton()]);
      if (input) {
        try {
          setLoading(true);
          const { success, order: newOrder } = await shipOrder(order.id, lines, tracking);
          if (success) {
            dispatchUpdateOrder(newOrder);
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
  }

  async function refund(origLines: Array<IMollieOrderLine>) {
    let lines = null;
    const { translations, order, dispatchUpdateOrder } = props;

    const reviewWrapper = document.createElement('DIV');
    render(<OrderLinesEditor lineType="refundable" translations={translations} lines={origLines} edited={newLines => lines = newLines}/>, reviewWrapper);
    let el = reviewWrapper.firstChild;

    // @ts-ignore
    let input = await swal({
      title: xss(translations.reviewShipment),
      text: xss(translations.reviewShipmentProducts),
      buttons: [xss(translations.cancel), xss(translations.OK)],
      closeOnClickOutside: false,
      content: el,
    });
    if (input) {
      try {
        setLoading(true);
        const { success, order: newOrder } = await refundOrder(order.id, lines);
        if (success) {
          dispatchUpdateOrder(newOrder);
        } else {
          swal({
            icon: 'error',
            title: xss(translations.anErrorOccurred),
            text: xss(translations.unableToRefund),
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

  async function cancel(origLines: Array<IMollieOrderLine>) {
    let lines = null;
    const { translations, order, dispatchUpdateOrder } = props;

    const reviewWrapper = document.createElement('DIV');
    render(<OrderLinesEditor lineType="cancelable" translations={translations} lines={origLines} edited={newLines => lines = newLines}/>, reviewWrapper);
    let el = reviewWrapper.firstChild;

    // @ts-ignore
    let input = await swal({
      title: xss(translations.reviewShipment),
      text: xss(translations.reviewShipmentProducts),
      buttons: [xss(translations.cancel), xss(translations.OK)],
      closeOnClickOutside: false,
      content: el,
    });
    if (input) {
      try {
        setLoading(true);
        const { success, order: newOrder } = await cancelOrder(order.id, lines);
        if (success) {
          dispatchUpdateOrder(newOrder);
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

  const { order, currencies, config: { legacy }, viewportWidth } = props;

  return (
    <TableContainer
      className={classnames({
        'table-responsive': !legacy,
      })}
      {...props}
    >
      <table className={classnames({
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
              <td className={classnames({
                'actions': !legacy,
              })}>
                <OrderLinesTableActions
                  loading={loading}
                  line={line}
                  refundLine={refund}
                  shipLine={ship}
                  cancelLine={cancel}
                />
              </td>
            </tr>
          ))}
        </tbody>
        <OrderLinesTableFooter loading={loading} ship={ship} refund={refund} cancel={cancel}/>
      </table>
    </TableContainer>
  );
}

export default connect<{}, {}, IProps>(
  (state: IMollieOrderState): Partial<IProps> => ({
    order: state.order,
    currencies: state.currencies,
    translations: state.translations,
    config: state.config,
    viewportWidth: state.viewportWidth,
  }),
  (dispatch: Dispatch): Partial<IProps> => ({
    dispatchUpdateOrder(order: IMollieApiOrder) {
      dispatch(updateOrder(order));
    }
  })
)(OrderLinesTable);
