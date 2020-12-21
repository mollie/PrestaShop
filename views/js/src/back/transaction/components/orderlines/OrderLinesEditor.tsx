/**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 * @link        https://github.com/mollie/PrestaShop
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 * @codingStandardsIgnoreStart
 */
import React, { ReactElement, useEffect, useState } from 'react';
import { Table, Tr } from 'styled-table-component';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faChevronDown, faTimesCircle } from '@fortawesome/free-solid-svg-icons'
import styled from 'styled-components';
import { cloneDeep, compact, filter, findIndex, forEach, range, remove } from 'lodash';

import { IMollieOrderLine, ITranslations } from '@shared/globals';

interface IProps {
  lineType: 'shippable' | 'refundable' | 'cancelable';
  translations: ITranslations;
  lines: Array<IMollieOrderLine>;
  edited: (newLines: Array<IMollieOrderLine>) => void;
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
&&&& {
  cursor: pointer!important;
  appearance: none!important;
  color: #555!important;
  border: 0!important;
  background-color: transparent!important;
  display: inline-block!important;
  height: auto!important;
  padding: 0 25px 0 5px!important;
  font-size: medium!important;
}

` as any;

const QuantityOption = styled.option`
font-size: medium!important;
color: #555!important;
` as any;

export default function OrderLinesEditor({ edited, lines, lineType }: IProps): ReactElement<{}> {
  const [stateLines, setStateLines] = useState<Array<IMollieOrderLine>>(lines);

  function _updateQty(lineId: string, qty: number): void {
    let newLines = compact(cloneDeep(stateLines));
    newLines = filter(newLines, line => line[`${lineType}Quantity`] > 0);

    const newLineIndex = findIndex(newLines, item => item.id === lineId);
    if (newLineIndex < 0) {
      return;
    }

    if (qty > 0) {
      newLines[newLineIndex].newQuantity = qty;
    } else if (newLines.length > 1) {
      newLines.splice(newLineIndex, 1);
    }
    setStateLines(cloneDeep(newLines));
    forEach(newLines, (newLine) => {
      if (typeof newLine.quantity === 'undefined') {
        return;
      }

      newLine.quantity = newLine.newQuantity;
      delete newLine.newQuantity;
    });
    edited(newLines);
  }

  useEffect(() => {
    _updateQty(lines[0].id, lines[0][`${lineType}Quantity`]);
  }, []);

  const renderLines = cloneDeep(stateLines);
  remove(renderLines, line => line[`${lineType}Quantity`] < 1);

  return (
    <Table bordered>
      <tbody>
        {stateLines.map((line) => (
          <Tr key={line.id} light>
            <td style={{ color: '#555' }}>{line.name}</td>
            <td style={{ color: '#555' }}>
              <QuantitySelect
                value={line.newQuantity || line[`${lineType}Quantity`]}
                onChange={({ target: { value: qty } }: any) => _updateQty(line.id, parseInt(qty, 10))}
              >
                {range(1, line[`${lineType}Quantity`] + 1).map((qty) => (
                  <QuantityOption key={qty} value={qty}>{qty}x</QuantityOption>
                ))}
              </QuantitySelect>
              <FontAwesomeIcon icon={faChevronDown} style={{ marginLeft: '-20px', pointerEvents: 'none' }}/>
            </td>
            <td style={{ display: stateLines.length > 1 ? 'auto' : 'none' }}>
              <CloseIcon icon={faTimesCircle} onClick={() => _updateQty(line.id, 0)}/>
            </td>
          </Tr>
        ))}
      </tbody>
    </Table>
  )
}
