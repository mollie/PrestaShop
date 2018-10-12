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
import OrderLinesTableHeader from './OrderLinesTableHeader';
import { connect } from 'react-redux';
import _ from 'lodash';
import { formatCurrency } from '../../misc/tools';
import OrderLinesTableFooter from './OrderLinesTableFooter';

interface IProps {
  // Redux
  order?: IMollieApiOrder,
  currencies?: ICurrencies,
}

class OrderLinesTable extends Component<IProps> {
  render() {
    const { order, currencies } = this.props;

    return (
      <div className="table-responsive">
        <table className="table">
          <OrderLinesTableHeader/>
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
                <td/>
              </tr>
            ))}
          </tbody>
          <OrderLinesTableFooter/>
        </table>
      </div>
    );
  }
}

export default connect<{}, {}, IProps>(
  (state: IMollieOrderState): Partial<IProps> => ({
    order: state.order,
    currencies: state.currencies,
  })
)(OrderLinesTable);
