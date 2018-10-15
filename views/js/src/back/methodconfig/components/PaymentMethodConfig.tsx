import React, { Component } from 'react';
import axios from 'axios';
import PaymentMethods from './PaymentMethods';
import LoadingDots from '../../misc/components/LoadingDots';

interface IProps {
  config: IMollieMethodConfig,
  translations: ITranslations,
  target: string,
}

interface IState {
  methods: Array<IMolliePaymentMethodItem>,
}

class PaymentMethodConfig extends Component<IProps> {
  state: IState = {
    methods: undefined,
  };

  componentDidMount() {
    const { config: { ajaxEndpoint }} = this.props;

    setTimeout(async () => {
      try {
        const { data: { methods } = { methods: null } } = await axios.post(ajaxEndpoint, {
          resource: 'orders',
          action: 'retrieve',
        });

        this.setState(() => ({ methods }));
      } catch (e) {
        console.error(e);

        this.setState(() => ({ methods: null }))
      }
    }, 0);
  }

  render() {
    const { target, translations, config } = this.props;
    const { methods } = this.state;

    if (typeof methods === 'undefined') {
      return <LoadingDots/>;
    }

    return <PaymentMethods methods={methods} translations={translations} target={target} config={config}/>;
  }
}

export default PaymentMethodConfig;
