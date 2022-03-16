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
import { arrayMove, SortableContainer } from 'react-sortable-hoc';
import styled from 'styled-components';
import { cloneDeep, find } from 'lodash';

import PaymentMethod from '@methodconfig/components/PaymentMethod';
import {
  IMollieMethodConfig,
  IMolliePaymentMethodConfigItem,
  IMolliePaymentMethodItem,
  ITranslations
} from '@shared/globals';

const Section = styled.section`
border: 2px solid #0c95fd!important;
` as any;

const Ul = styled.ul`
margin: -1px!important;
padding: 0;
` as any;

const SortableList = SortableContainer(({ items, translations, onArrowClicked, onToggle, config }: any): ReactElement<{}> => {
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
            tipEnableSSL={item.tipEnableSSL}
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

export default function PaymentMethods({ translations, config, methods: propsMethods, target }: IProps): ReactElement<{}> {
  const [methods, setMethods] = useState(propsMethods);

  function _onToggle(id: string, enabled: boolean): void {
    const newMethods = cloneDeep(methods);
    const method = find(newMethods, item => item.id === id);
    method.enabled = enabled;
    setMethods(newMethods);
  }

  function _onArrowClicked({ oldIndex, newIndex}: any): void {
    setMethods(arrayMove(cloneDeep(methods), oldIndex, newIndex));
  }

  function _onSortEnd({ oldIndex, newIndex }: any): void {
    setMethods(arrayMove(cloneDeep(methods), oldIndex, newIndex));
  }

  function _shouldCancelStart({ target }: any): boolean {
    return ['I', 'SVG', 'BUTTON', 'INPUT', 'SELECT', 'LABEL'].includes(target.tagName.toUpperCase());
  }

  useEffect(() => {
    const input: HTMLInputElement = document.getElementById(target) as HTMLInputElement;
    if (input != null) {
      input.value = JSON.stringify(methods.map((method: IMolliePaymentMethodItem, index: number): IMolliePaymentMethodConfigItem => ({
        ...method,
        position: index,
      })));
    }
  });

  return (
    <SortableList
      translations={translations}
      items={methods}
      onSortEnd={_onSortEnd}
      onArrowClicked={_onArrowClicked}
      onToggle={_onToggle}
      shouldCancelStart={_shouldCancelStart}
      config={config}
      helperClass="sortable-helper"
    />
  );
}
