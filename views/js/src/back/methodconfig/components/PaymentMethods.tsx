import React, { Component } from 'react';
import { SortableContainer, arrayMove } from 'react-sortable-hoc';
import _ from 'lodash';
import styled from 'styled-components';

import PaymentMethod from './PaymentMethod';

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
            translations={translations}
            position={index}
            max={items.length - 1}
            name={item.name}
            moveMethod={onArrowClicked}
            onToggle={onToggle}
          />
        ))}
      </Ul>
    </Section>
  );
});

interface IProps {
  methods: Array<IMolliePaymentMethodItem>,
  translations: ITranslations,
  target: string,
  config: IMollieMethodConfig,
}

interface IState {
  methods: Array<IMolliePaymentMethodItem>,
}

class PaymentMethods extends Component<IProps> {
  state: IState = {
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
    const methods = _.cloneDeep(this.state.methods);
    const method = _.find(methods, item => item.id === id);
    method.enabled = enabled;
    this.setState(() => ({ methods }));
  };

  onArrowClicked = ({ oldIndex, newIndex}: any) => {
    this.setState(() => ({
      methods: arrayMove(_.cloneDeep(this.state.methods), oldIndex, newIndex),
    }));
  };

  onSortEnd = ({ oldIndex, newIndex }: any) => {
    this.setState(() => ({
      methods: arrayMove(_.cloneDeep(this.state.methods), oldIndex, newIndex),
    }));
  };

  shouldCancelStart = ({ target }: any) => {
    return _.includes(['I', 'SVG', 'BUTTON', 'INPUT', 'SELECT', 'LABEL'], target.tagName.toUpperCase());
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
      />
    );
  }
}

export default PaymentMethods;
