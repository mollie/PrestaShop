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
import React, { Component } from 'react';
import { SortableContainer, arrayMove } from 'react-sortable-hoc';
import styled from 'styled-components';
import { cloneDeep, find } from 'lodash';

import PaymentMethod from './PaymentMethod';
import {
  IMollieMethodConfig,
  IMolliePaymentMethodConfigItem,
  IMolliePaymentMethodItem,
  ITranslations
} from '../../../globals';

const Section = styled.section`
border: 2px solid #0c95fd!important;
` as any;

const Ul = styled.ul`
margin: -1px!important;
padding: 0;
` as any;

const SortableList = SortableContainer(({ items, translations, onArrowClicked, onToggle, config }: any) => {
  return (
    <Section className="module_list" style={{ maxWidth: '440px' }}>
      <Ul>
        {items.map((item: IMolliePaymentMethodItem, index: number) => (
          <PaymentMethod
            imageUrl={item.image.svg ? item.image.svg : `${config.moduleDir}views/img/${item.id}.svg`}
            key={item.id}
            index={index}
            code={item.id}
            enabled={item.enabled}
            available={item.available}
            translations={translations}
            position={index}
            max={items.length - 1}
            name={item.name}
            moveMethod={onArrowClicked}
            onToggle={onToggle}
            config={config}
          />
        ))}
      </Ul>
    </Section>
  );
});

interface IProps {
  methods: Array<IMolliePaymentMethodItem>;
  translations: ITranslations;
  target: string;
  config: IMollieMethodConfig;
}

interface IState {
  methods: Array<IMolliePaymentMethodItem>;
}

class PaymentMethods extends Component<IProps> {
  readonly state: IState = {
    methods: this.props.methods,
  };

  componentDidMount() {
    this.componentDidUpdate();
  }

  componentDidUpdate() {
    const input: HTMLInputElement = document.getElementById(this.props.target) as HTMLInputElement;
    if (input != null) {
      input.value = JSON.stringify(this.state.methods.map((method: IMolliePaymentMethodItem, index: number): IMolliePaymentMethodConfigItem => ({
        ...method,
        position: index,
      })));
    }
  }

  onToggle = (id: string, enabled: boolean) => {
    const methods = cloneDeep(this.state.methods);
    const method = find(methods, item => item.id === id);
    method.enabled = enabled;
    this.setState({ methods });
  };

  onArrowClicked = ({ oldIndex, newIndex}: any) => {
    this.setState({
      methods: arrayMove(cloneDeep(this.state.methods), oldIndex, newIndex),
    });
  };

  onSortEnd = ({ oldIndex, newIndex }: any) => {
    this.setState({
      methods: arrayMove(cloneDeep(this.state.methods), oldIndex, newIndex),
    });
  };

  shouldCancelStart = ({ target }: any) => {
    return ['I', 'SVG', 'BUTTON', 'INPUT', 'SELECT', 'LABEL'].includes(target.tagName.toUpperCase());
  };

  render() {
    const { methods } = this.state;
    const { translations, config } = this.props;

    return (
      <SortableList
        translations={translations}
        items={methods}
        onSortEnd={this.onSortEnd}
        onArrowClicked={this.onArrowClicked}
        onToggle={this.onToggle}
        shouldCancelStart={this.shouldCancelStart}
        config={config}
        helperClass="sortable-helper"
      />
    );
  }
}

export default PaymentMethods;
