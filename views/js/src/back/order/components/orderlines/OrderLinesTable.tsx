/**
 * Copyright (c) 2012-2018, Mollie B.V.
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
import { render } from 'react-dom';
import OrderLinesTableHeader from './OrderLinesTableHeader';
import { connect } from 'react-redux';
import _ from 'lodash';
import { formatCurrency } from '../../misc/tools';
import OrderLinesTableFooter from './OrderLinesTableFooter';
import xss from 'xss';
import OrderLinesEditor from './OrderLinesEditor';
import ShipmentTrackingEditor from './ShipmentTrackingEditor';
import { shipOrder } from '../../misc/ajax';
import { Dispatch } from 'redux';
import { updateOrder } from '../../store/actions';

interface IProps {
  // Redux
  order?: IMollieApiOrder,
  currencies?: ICurrencies,
  translations?: ITranslations,

  dispatchUpdateOrder?: Function,
}

interface IState {
  loading: boolean,
}

class OrderLinesTable extends Component<IProps> {
  state: IState = {
    loading: false,
  };

  ship = async (origLines: Array<IMollieOrderLine>) => {
    let lines = null;
    const { translations, order, dispatchUpdateOrder } = this.props;

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
      let tracking: IMollieTracking = null;

      let trackingWrapper = document.createElement('DIV');
      trackingWrapper.innerHTML = '';
      render(<ShipmentTrackingEditor translations={translations} edited={newTracking => tracking = newTracking}/>, trackingWrapper);
      el = trackingWrapper.firstChild;
      // @ts-ignore
      input = await swal({
        title: xss(translations.trackingDetails),
        text: xss(translations.addTrackingInfo),
        buttons: [xss(translations.cancel), xss(translations.shipProducts)],
        closeOnClickOutside: false,
        content: el,
      });
      if (input) {
        try {
          this.setState(() => ({ loading: true }));
          const { success, order: newOrder } = await shipOrder(order.id, lines, tracking);
          if (success) {
            dispatchUpdateOrder(newOrder);
          }
        } catch (e) {
          console.error(e);
        } finally {
          this.setState(() => ({ loading: false }));
        }
      }
    }
  };

  render() {
    const { loading } = this.state;
    const { order, currencies, translations } = this.props;

    return (
      <div className="table-responsive">
        <table className="table">
          <OrderLinesTableHeader loading={loading}/>
          <tbody>
            {order.lines.map((line: IMollieOrderLine) => (
              <tr key={line.id} style={{ marginBottom: '100px' }}>
                <td><strong>{line.quantity}x</strong> {line.name}</td>
                <td>{line.status}</td>
                <td>{line.quantityShipped}</td>
                <td>{line.quantityCanceled}</td>
                <td>{line.quantityRefunded}</td>
                <td>{formatCurrency(parseFloat(line.unitPrice.value), _.get(currencies, line.unitPrice.currency))}</td>
                <td>{formatCurrency(parseFloat(line.vatAmount.value), _.get(currencies, line.vatAmount.currency))} ({line.vatRate}%)</td>
                <td>{formatCurrency(parseFloat(line.totalAmount.value), _.get(currencies, line.totalAmount.currency))}</td>
                <td className="actions">
                  <div className="btn-group-action">
                    <div className="btn-group pull-right">
                      <button className=" btn btn-default" title="" disabled={loading} onClick={() => this.ship([line])}>
                        <i className="icon icon-truck"/> {translations.ship}
                      </button>
                      {/*<button type="button" className="btn btn-default dropdown-toggle" data-toggle="dropdown">*/}
                        {/*<span className="caret">&nbsp;</span>*/}
                      {/*</button>*/}
                      {/*<ul className="dropdown-menu">*/}
                        {/*<li><a className="" href="index.php?controller=AdminModules&amp;token=e69ed5725e4389835af0cb7ffa3c1d6c&amp;module_name=izettle&amp;enable=0&amp;tab_module=payments_gateways"*/}
                                {/*title=""><i className="icon-off"></i> Uitschakelen</a></li>*/}
                        {/*<li><a className=""*/}
                               {/*href="index.php?controller=AdminModules&amp;token=e69ed5725e4389835af0cb7ffa3c1d6c&amp;module_name=izettle&amp;disable_device=4&amp;tab_module=payments_gateways"*/}
                               {/*onClick="" title="Uitschakelen op mobielen"><i className="icon-mobile"></i> Uitschakelen op mobielen</a></li>*/}
                        {/*<li><a className=""*/}
                               {/*href="index.php?controller=AdminModules&amp;token=e69ed5725e4389835af0cb7ffa3c1d6c&amp;module_name=izettle&amp;disable_device=2&amp;tab_module=payments_gateways"*/}
                               {/*onClick="" title="Uitschakelen op tablets"><i className="icon-tablet"></i> Uitschakelen op tablets</a></li>*/}
                        {/*<li><a className=""*/}
                               {/*href="index.php?controller=AdminModules&amp;token=e69ed5725e4389835af0cb7ffa3c1d6c&amp;module_name=izettle&amp;disable_device=1&amp;tab_module=payments_gateways"*/}
                               {/*onClick="" title="Uitschakelen op computers"><i className="icon-desktop"></i> Uitschakelen op computers</a></li>*/}
                        {/*<li><a className="" href="index.php?controller=AdminModules&amp;token=e69ed5725e4389835af0cb7ffa3c1d6c&amp;module_name=izettle&amp;reset&amp;tab_module=payments_gateways"*/}
                               {/*onClick="" title=""><i className="icon-undo"></i> Herstellen</a></li>*/}
                        {/*<li><a className=""*/}
                               {/*href="index.php?controller=AdminModules&amp;token=e69ed5725e4389835af0cb7ffa3c1d6c&amp;uninstall=izettle&amp;tab_module=payments_gateways&amp;module_name=izettle&amp;anchor=Izettle"*/}
                               {/*onClick="return confirm('Weet u zeker dat u deze module wilt deïnstalleren?');"*/}
                               {/*title="Deïnstalleren"><i className="icon-minus-sign-alt"></i> Deïnstalleren</a></li>*/}
                        {/*<li><a className="action_unfavorite toggle_favorite" data-value="0" data-module="izettle"*/}
                               {/*style="" href="#" onClick="" title="Verwijder van Favorieten"><i*/}
                          {/*className="icon-star"></i> Verwijder van Favorieten</a></li>*/}
                        {/*<li><a className="action_favorite toggle_favorite" data-value="1" data-module="izettle"*/}
                               {/*style="display:none;" href="#" onClick="" title="Markeer as Favoriet"><i*/}
                          {/*className="icon-star"></i> Markeer as Favoriet</a></li>*/}
                        {/*<li className="divider"></li>*/}
                        {/*<li><a className="text-danger"*/}
                               {/*href="index.php?controller=AdminModules&amp;token=e69ed5725e4389835af0cb7ffa3c1d6c&amp;delete=izettle&amp;tab_module=payments_gateways&amp;module_name=izettle"*/}
                               {/*onClick="return confirm('Deze actie zal de module voorgoed van de server verwijderen. Weet u het zeker?');" title=""><i className="icon-trash"></i> Verwijder</a></li>*/}
                      {/*</ul>*/}
                    </div>
                  </div>
                </td>
              </tr>
            ))}
          </tbody>
          <OrderLinesTableFooter loading={loading}/>
        </table>
      </div>
    );
  }
}

export default connect<{}, {}, IProps>(
  (state: IMollieOrderState): Partial<IProps> => ({
    order: state.order,
    currencies: state.currencies,
    translations: state.translations,
  }),
  (dispatch: Dispatch): Partial<IProps> => ({
    dispatchUpdateOrder(order: IMollieApiOrder) {
      dispatch(updateOrder(order));
    }
  })
)(OrderLinesTable);
