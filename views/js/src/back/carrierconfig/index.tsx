import React from 'react';
import { render } from 'react-dom';

import CarrierConfig from './components/CarrierConfig';

export const carrierConfig = (
  target: string,
  config: Array<IMollieCarrierConfig>,
  translations: ITranslations
) => {
  return render(
    <CarrierConfig translations={translations} config={config} target={target}/>,
    document.getElementById(`${target}_container`)
  );
};
