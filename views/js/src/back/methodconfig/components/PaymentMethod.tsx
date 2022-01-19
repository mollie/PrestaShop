/**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 * @link        https://github.com/mollie/PrestaShop
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 * @codingStandardsIgnoreStart
 */
import React, { ReactElement, useEffect } from 'react';
import styled from 'styled-components';
import { SortableElement } from 'react-sortable-hoc';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { faChevronDown, faChevronUp, faExclamationTriangle } from '@fortawesome/free-solid-svg-icons';

import Switch from '@methodconfig/components/Switch';
import { IMollieMethodConfig, ITranslations } from '@shared/globals';

interface IProps {
  position: number;
  max: number;
  enabled: boolean;
  available: boolean;
  tipEnableSSL: boolean;
  name: string;
  code: string;
  imageUrl: string;
  translations: ITranslations;
  config: IMollieMethodConfig;

  moveMethod: Function;
  onToggle: Function;
}

declare let $: any;

const Li = styled.li`
cursor: move!important;
-webkit-touch-callout: none; /* iOS Safari */
   -webkit-user-select: none; /* Safari */
    -khtml-user-select: none; /* Konqueror HTML */
      -moz-user-select: none; /* Firefox */
       -ms-user-select: none; /* Internet Explorer/Edge */
           user-select: none; /* Non-prefixed version, currently
                                 supported by Chrome and Opera */
                                 
border: 1px solid #0c95fd;
border-bottom-width: ${(props: any) => props.last ? '1px' : '2px'};
background-color: #fff;
display: table;
width: 100%;
padding: 5px 0;
margin-bottom: -1px;
${({ legacy }: any) => legacy ? 'width: calc(100% - 2px);' : ''}
` as any;

const PositionColumn = styled.div`
display: table-cell;
width: 80px!important;
vertical-align: middle!important;
text-align: right!important;
` as any;

const IconColumn = styled.div`
display: table-cell;
width: 75px;
text-align: left;
vertical-align: middle;
` as any;

const InfoColumn = styled.div`
display: table-cell;
height: 50px;
vertical-align: middle;
box-sizing: border-box;
` as any;

const PositionIndicator = styled.span`
border: solid 1px #ccc;
background-color: #eee;
padding: 0;
font-size: 1.4em;
color: #aaa;
cursor: move;
text-shadow: #fff 1px 1px;
-webkit-border-radius: 3px;
border-radius: 3px;
-webkit-box-shadow: rgba(0,0,0,0.2) 0 1px 3px inset;
box-shadow: rgba(0,0,0,0.2) 0 1px 3px inset;
display: block;
width: 40px;
text-align: center;
margin: 0 auto;
${({ legacy }: any) => legacy ? 'height: 24px;line-height: 24px;' : ''}
` as any;

const ArrowButton = styled.button`
padding: 1px 5px;
font-size: 11px;
line-height: 1.5;
border-radius: 3px;
color: #363A41;
display: inline-block;
margin-bottom: 0;
font-weight: normal;
text-align: center;
vertical-align: middle;
cursor: pointer;
background: #2eacce none;
border: 1px solid #0000;
white-space: nowrap;
font-family: inherit;
width: 26px!important;
margin-top: 6px!important;
margin-right: 2px!important;

:hover {
  background-color: #008abd;
  -webkit-box-shadow: none;
  box-shadow: none;
}

:disabled {
  background-color: #2eacce;
  border-color: #2eacce;
  cursor: not-allowed;
  opacity: .65;
  filter: alpha(opacity=65);
  -webkit-box-shadow: none;
  box-shadow: none;
}
${({ legacy }: any) => legacy ? 'height: 20px;line-height: 20px;' : ''}
` as any;

const ButtonBox = styled.div`
margin: 0 auto;
text-align: center;
` as any;

function PaymentMethod({
  enabled,
  onToggle,
  code,
  translations,
  position,
  max,
  name,
  imageUrl,
  moveMethod,
  available,
                         tipEnableSSL,
  config: { legacy }
}: IProps
): ReactElement<{}> {
  function _toggleMethod(enabled: boolean): void {
    onToggle(code, enabled);
  }

  useEffect(() => {
    if (typeof $ !== 'undefined' && typeof $.prototype.tooltip === 'function') {
      $('[data-toggle=tooltip]').tooltip();
    }
  });

  return (
    <Li last={position >= max} legacy={legacy}>
      <PositionColumn>
        <PositionIndicator legacy={legacy}>{position + 1}</PositionIndicator>
        <ButtonBox>
          <ArrowButton
            legacy={legacy}
            disabled={position <= 0}
            onClick={(e: any) => {
              e.preventDefault();
              moveMethod({
                oldIndex: position - 1,
                newIndex: position,
              });
            }}
          >
            <FontAwesomeIcon icon={faChevronUp} style={{ color: 'white', pointerEvents: 'none' }}/>
          </ArrowButton>
          <ArrowButton
            legacy={legacy}
            disabled={position >= max}
            onClick={(e: any) => {
              e.preventDefault();
              moveMethod({
                oldIndex: position + 1,
                newIndex: position,
              });
            }}
          >
            <FontAwesomeIcon icon={faChevronDown} style={{ color: 'white', pointerEvents: 'none' }}/>
          </ArrowButton>
        </ButtonBox>
      </PositionColumn>
      <IconColumn>
        <img
          width="57"
          src={imageUrl}
          alt={name}
          style={{
            width: '57px',
          }}
        />
      </IconColumn>
      <InfoColumn>
        <div style={{ display: 'inline-block', marginTop: '5px' }}>
          <span>
            {name}
          </span>
        </div>
        {!available && tipEnableSSL &&(
          <p
            style={{
              float: 'right',
              marginRight: '20px'
            }}
            title={translations.thisPaymentMethodNeedsSSLEnabled}
            data-toggle="tooltip"
          >
            <FontAwesomeIcon icon={faExclamationTriangle}/> {translations.notAvailable}
          </p>
        )}
        {!available && !tipEnableSSL &&(
          <p
            style={{
              float: 'right',
              marginRight: '20px'
            }}
            title={translations.thisPaymentMethodIsNotAvailableOnPaymentsApi}
            data-toggle="tooltip"
          >
            <FontAwesomeIcon icon={faExclamationTriangle}/> {translations.notAvailable}
          </p>
        )}
        {available &&
        <Switch
          id={code}
          translations={translations}
          enabled={enabled}
          onChange={({ target: { value } }: any) => _toggleMethod(!!value)}
          legacy={legacy}
        />
        }
      </InfoColumn>
    </Li>
  );
}

export default SortableElement(PaymentMethod);
