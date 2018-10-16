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
import _ from 'lodash';
// @ts-ignore
import { Table, Tr } from 'styled-table-component';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faTimesCircle, faChevronDown } from '@fortawesome/free-solid-svg-icons'
import styled from 'styled-components';

interface IProps {
  lineType: 'shippable' | 'refundable' | 'cancelable',
  translations: ITranslations,
  lines: Array<IMollieOrderLine>,
  edited: (newLines: Array<IMollieOrderLine>) => void,
}

interface IState {
  lines: Array<IMollieOrderLine>,
}


const CloseIcon = styled(FontAwesomeIcon)`
cursor: pointer;
color: #555;

:hover {
  opacity: 0.8;
}
:active {
  opacity: 0.6;
}
` as any;

const QuantitySelect = styled.select`
cursor: pointer!important;
-moz-appearance: none!important;
-webkit-appearance: none!important;
appearance: none!important;
color: #555!important;
border: 0!important;
background-color: transparent!important;
display: inline-block!important;
height: auto!important;
padding: 0 25px 0 5px!important;
font-size: medium!important;
` as any;

const QuantityOption = styled.option`
font-size: medium!important;
color: #555!important;
` as any;

class OrderLinesEditor extends Component<IProps> {
  state: IState = {
    lines: this.props.lines,
  };

  componentDidMount() {
    const { lines, lineType } = this.props;

    this.updateQty(lines[0].id, lines[0][`${lineType}Quantity`])
  }

  updateQty = (lineId: string, qty: number) => {
    const { lines } = this.state;
    const { edited, lineType } = this.props;
    let newLines = _.compact(_.cloneDeep(lines));
    newLines = _.filter(newLines, line => line[`${lineType}Quantity`] > 0);

    const newLineIndex = _.findIndex(newLines, item => item.id === lineId);
    if (newLineIndex < 0) {
      return;
    }

    if (qty > 0) {
      newLines[newLineIndex].newQuantity = qty;
    } else if (newLines.length > 1) {
      newLines.splice(newLineIndex, 1);
    }
    const stateLines = _.cloneDeep(newLines);
    this.setState(() => ({
      lines: stateLines,
    }));
    _.forEach(newLines, (newLine) => {
      if (typeof newLine.quantity === 'undefined') {
        return;
      }

      newLine.quantity = newLine.newQuantity;
      delete newLine.newQuantity;
    });
    edited(newLines);
  };

  render() {
    const { lineType } = this.props;
    const { lines: origLines } = this.state;
    const lines = _.cloneDeep(origLines);
    _.remove(lines, line => line[`${lineType}Quantity`] < 1);


    return (
      <Table bordered>
        <tbody>
          {lines.map((line) => (
            <Tr key={line.id}>
              <td style={{ color: '#555' }}>{line.name}</td>
              <td style={{ color: '#555' }}>
                <QuantitySelect
                  value={line.newQuantity || line[`${lineType}Quantity`]}
                  onChange={({ target: { value: qty }}: any) => this.updateQty(line.id, parseInt(qty, 10))}
                >
                  {_.range(1, line[`${lineType}Quantity`] + 1).map((qty) => (
                    <QuantityOption key={qty} value={qty}>{qty}x</QuantityOption>
                  ))}
                </QuantitySelect>
                <FontAwesomeIcon icon={faChevronDown} style={{ marginLeft: '-20px', pointerEvents: 'none' }}/>
              </td>
              <td style={{ display: lines.length > 1 ? 'auto' : 'none' }}>
                <CloseIcon icon={faTimesCircle} onClick={() => this.updateQty(line.id, 0)}/>
              </td>
            </Tr>
          ))}
        </tbody>
        {/*<tfoot>*/}
          {/*<Tr light>*/}
            {/*<td colSpan={3}>Test</td>*/}
          {/*</Tr>*/}
        {/*</tfoot>*/}
      </Table>
    )
  }
}

export default OrderLinesEditor;
