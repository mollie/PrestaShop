import React, { Component, Fragment } from 'react';
import _ from 'lodash';

interface IProps {
  config: Array<IMollieCarrierConfig>,
  translations: ITranslations,
  target: string,
}

interface IState {
  config: Array<IMollieCarrierConfig>,
}

class CarrierConfig extends Component<IProps> {
  state: IState = {
    config: this.props.config,
  };

  updateCarrierConfig = (id: string, key: string, value: string|null) => {
    const localConfig = _.cloneDeep(this.state.config);

    const config = _.find(localConfig, item => item.id_carrier === id);
    if (typeof config === 'undefined') {
      return;
    }
    config[key] = value;

    this.setState(() => ({
      config: localConfig,
    }));
  };

  render() {
    const { config } = this.state;
    const { translations, target } = this.props;

    return (
      <Fragment>
        <div className="alert alert-info">Check the source of your carrier</div>
        <table className="list form alternate table">
          <thead>
            <tr>
              <td className="left">{translations.name}</td>
              <td className="left">{translations.urlSource}</td>
              <td className="left">{translations.customUrl}</td>
            </tr>
          </thead>
          <tbody>
            {config.map((carrier) => (
              <tr key={carrier.id_carrier}>
                <td className="left">
                  {carrier.name}
                </td>
                <td className="left">
                  <select
                    value={carrier.source}
                    onChange={({ target: { value } }) => this.updateCarrierConfig(carrier.id_carrier, 'source', value)}
                  >
                    <option value="carrier_url">{translations.carrierUrl}</option>
                    <option value="custom_url">{translations.customUrl}</option>
                    <option value="module">{translations.module}</option>
                  </select>
                </td>
                <td className="left">
                  <input
                    type="text"
                    disabled={carrier.source !== 'custom_url'}
                    value={carrier.custom_url}
                    onChange={({ target: { value } }) => this.updateCarrierConfig(carrier.id_carrier, 'custom_url', value)}
                  />
                </td>
              </tr>
            ))}
          </tbody>
        </table>
        <input type="hidden" id={target} name={target} value={JSON.stringify(this.state.config)}/>
      </Fragment>
    );
  }
}

export default CarrierConfig;
