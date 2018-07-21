import axios from 'axios';
import swal from 'sweetalert';
import _ from 'lodash';
import xss from 'xss';

const showError = (message) => {
  swal({
    icon: 'error',
    title: _.get(document, 'documentElement.lang', 'en') === 'nl' ? 'Fout' : 'Error',
    text: xss(message),
  }).then();
};

const handleClick = async (button, config, translations) => {
  const steps = [
    {
      action: 'downloadUpdate',
      defaultError: translations.unableToConnect,
    },
    {
      action: 'downloadUpdate',
      defaultError: translations.unableToUnzip,
    },
    {
      action: 'downloadUpdate',
      defaultError: translations.unableToConnect,
    },
  ];

  _.forEach(steps, async (step) => {
    try {
      const { data } = await axios.get(`${config.endpoint}&action=${step.action}`);
      if (!_.get(data, 'success')) {
        showError(_.get(data, 'message', step.defaultError));
      }
    } catch (e) {
      console.error(e);
      showError(step.defaultError);
    }
  });

  swal({
    icon: 'success',
    text: translations.updated
  }).then();
};

const init = (button, config, translations) => {
  button.onclick = () => handleClick(button, config, translations);
};

