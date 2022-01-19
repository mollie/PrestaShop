/**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 * @link        https://github.com/mollie/PrestaShop
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 * @codingStandardsIgnoreStart
 */
import React, { ReactElement } from 'react';
import styled from 'styled-components';
import { lighten, rgba } from 'polished';

import { ITranslations } from '@shared/globals';

interface IProps {
  enabled: boolean;
  onChange: any;
  id: string;
  translations: ITranslations;
  legacy: boolean;
}

const Span = styled.span`
display: block;
height: 26px;
float: right;
width: 100px;
right: 20px;

a {
  display: block;
  transition: all 0.3s ease-out;
}
label,
> span {
  line-height: 26px;
  vertical-align: middle;
}

* {
  box-sizing: border-box; 
  outline: 0!important
}

position: relative;
input {
  position: absolute;
  opacity: 0;
}

label {
  position: relative;
  z-index: 2;
  width: 50%;
  height: 100%;
  margin: ${({ legacy }: IProps) => legacy ? '-2px' : '0'} 0 0 0;
  text-align: center;
  float: left;
}

a {
  position: absolute;
  top: 0;
  padding: 0;
  z-index: 1;
  width: 50%;
  height: 100%;
  color: white;
  border: solid 1px #279CBB!important;
  background-color: #2EACCE!important;
  left: 0;
  border-radius: 3px!important;
}

input:last-of-type:checked ~ a {
  border: solid 1px #CA6F6F!important;
  background-color: #E08F95!important;
  left: 50% ;
  border-radius: 3px!important;
}

input:disabled ~ a {
  border: solid 1px lighten(gray,20%) !important;
  background-color: lighten(gray,30%)	!important;
  // box-shadow: ${lighten(0.2, 'gray')} 0 -1px 0 inset !important;
}

margin-top: ${({ legacy }: IProps) => legacy ? '0' : '3px'};
background-color: #eee;
border-radius: 3px!important;
color: #555;
text-align: center;
box-shadow: ${rgba('black', 0.15)} 0 1px 4px 1px inset;

label {
  text-transform: uppercase;
  color: #bbb;
  font-weight: 400;
  cursor: pointer;
  transition: color 0.2s ease-out;
}

input:checked + label {
  color: white
}

> span {
  color: #666;
  text-transform: uppercase;
  cursor: pointer;
}
` as any;

export default function Switch(props: IProps): ReactElement<{}> {
  const { enabled, onChange, id, translations } = props;

  return (
    <Span {...props}>
      <input
        type="radio"
        data-mollie-check=""
        name={`MOLLIE_METHOD_ENABLED_${id}`}
        id={`MOLLIE_METHOD_ENABLED_on_${id}`}
        value="1"
        checked={enabled}
        onChange={onChange}
      />
      <label
        htmlFor={`MOLLIE_METHOD_ENABLED_on_${id}`}
      >
        {translations.yes.toUpperCase()}
      </label>
      <input
        type="radio"
        name={`MOLLIE_METHOD_ENABLED_${id}`}
        id={`MOLLIE_METHOD_ENABLED_off_${id}`}
        value=""
        checked={!enabled}
        onChange={onChange}
      />
      <label
        htmlFor={`MOLLIE_METHOD_ENABLED_off_${id}`}
      >
        {translations.no.toUpperCase()}
      </label> {
      // eslint-disable-next-line jsx-a11y/anchor-has-content
      } <a className="slide-button btn"/>
    </Span>
  );
}
