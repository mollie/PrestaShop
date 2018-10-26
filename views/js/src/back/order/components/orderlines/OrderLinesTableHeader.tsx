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

interface IProps {
  // Redux
  translations?: ITranslations,
  viewportWidth?: number,
}

class OrderLinesTableHeader extends Component<IProps> {
  render() {
    const { translations, viewportWidth } = this.props;

    return (
      <thead>
        <tr>
          <th>
            <span className="title_box"><strong>{translations.product}</strong></span>
          </th>
          <th>
            <span className="title_box">{translations.status}</span>
          </th>
          {viewportWidth < 1390 && (
            <th>
              <span className="title_box">
                <span>{translations.shipped}</span>
                <br/> <span style={{ whiteSpace: 'nowrap' }}>/ {translations.canceled}</span>
                <br/> <span style={{ whiteSpace: 'nowrap' }}>/ {translations.refunded}</span>
                </span>
            </th>
          )}
          {viewportWidth >= 1390 && (
            <>
              <th>
                <span className="title_box">{translations.shipped}</span>
              </th>
              <th>
                <span className="title_box">{translations.canceled}</span>
              </th>
              <th>
                <span className="title_box">{translations.refunded}</span>
              </th>
            </>
          )}
          <th>
            <span className="title_box">{translations.unitPrice}</span>
          </th>
          <th>
            <span className="title_box">{translations.vatAmount}</span>
          </th>
          <th>
            <span className="title_box">{translations.totalAmount}</span>
          </th>
          <th/>
        </tr>
      </thead>
    );
  }
}

export default connect<{}, {}, IProps>(
  (state: IMollieOrderState): Partial<IProps> => ({
    translations: state.translations,
    viewportWidth: state.viewportWidth,
  })
)(OrderLinesTableHeader);
