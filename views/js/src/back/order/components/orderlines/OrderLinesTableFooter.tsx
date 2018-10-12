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
import { connect } from 'react-redux';
import _ from 'lodash';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { faCircleNotch, faTimes, faTruck, faUndoAlt } from '@fortawesome/free-solid-svg-icons';

interface IProps {
  loading: boolean,
  ship: Function,
  refund: Function,
  cancel: Function,

  // Redux
  order?: IMollieApiOrder,
  translations?: ITranslations,
}

class OrderLinesTableFooter extends Component<IProps> {
  get shippable() {
    for (let line of Object.values(this.props.order.lines)) {
      if (line.shippableQuantity >= 1) {
        return true;
      }
    }

    return false;
  }

  get refundable() {
    for (let line of Object.values(this.props.order.lines)) {
      if (line.refundableQuantity >= 1) {
        return true;
      }
    }

    return false;
  }

  get cancelable() {
    for (let line of Object.values(this.props.order.lines)) {
      if (line.cancelableQuantity >= 1) {
        return true;
      }
    }

    return false;
  }

  render() {
    const { translations, loading, order, ship, cancel, refund } = this.props;

    return (
      <tfoot>
        <tr>
          <td colSpan={10}>
            <button
              style={{ float: 'right', marginLeft: '5px', display: this.shippable ? 'block' : 'none' }}
              type="button"
              onClick={() => ship(_.compact(order.lines))}
              className="btn btn-primary"
              disabled={loading}
            >
              <FontAwesomeIcon icon={loading ? faCircleNotch : faTruck} spin={loading}/> {translations.shipAll}
            </button>
            <button
              style={{ float: 'right', marginLeft: '5px', display: this.refundable ? 'block' : 'none' }}
              type="button"
              onClick={() => refund(_.compact(order.lines))}
              className="btn btn-default"
              disabled={loading}
            >
              <FontAwesomeIcon icon={loading ? faCircleNotch : faUndoAlt} spin={loading}/> {translations.refundAll}
            </button>
            <button
              style={{ float: 'right', marginLeft: '5px', display: this.cancelable ? 'block' : 'none' }}
              type="button"
              onClick={() => cancel(_.compact(order.lines))}
              className="btn btn-default"
              disabled={loading}
            >
              <FontAwesomeIcon icon={loading ? faCircleNotch : faTimes} spin={loading}/> {translations.cancelAll}
            </button>
          </td>
        </tr>
      </tfoot>
    );
  }
}

export default connect<{}, {}, IProps>(
  (state: IMollieOrderState): Partial<IProps> => ({
    translations: state.translations,
    order: state.order,
  })
)(OrderLinesTableFooter);
