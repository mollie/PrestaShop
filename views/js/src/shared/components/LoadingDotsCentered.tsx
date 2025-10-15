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

const SpinnerDiv = styled.div`
&&&& {
  position: absolute;
  top: 110px;
  left: 90px;
}

&&&& > div {
  width: 18px;
  height: 18px;
  background-color: #333;

  border-radius: 100%;
  display: inline-block;
  -webkit-animation: sk-bouncedelay 1.4s infinite ease-in-out both;
  animation: sk-bouncedelay 1.4s infinite ease-in-out both;
}

&&&& .bounce1 {
  -webkit-animation-delay: -0.32s;
  animation-delay: -0.32s;
}

&&&& .bounce2 {
  -webkit-animation-delay: -0.16s;
  animation-delay: -0.16s;
}

@-webkit-keyframes sk-bouncedelay {
  0%, 80%, 100% { -webkit-transform: scale(0) }
  40% { -webkit-transform: scale(1.0) }
}

@keyframes sk-bouncedelay {
0%, 80%, 100% {
  -webkit-transform: scale(0);
  transform: scale(0);
  } 40% {
    -webkit-transform: scale(1.0);
    transform: scale(1.0);
  }
}
`;

export default function LoadingDotsCentered(): ReactElement<{}> {
  return (
    <SpinnerDiv>
      <div className="bounce1"/>
      <div className="bounce2"/>
      <div className="bounce3"/>
    </SpinnerDiv>
  );
}
