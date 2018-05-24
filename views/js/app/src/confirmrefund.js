import 'babel-polyfill';
import 'raf/polyfill';
import swal from 'sweetalert';
import xss from 'xss';

const refund = (callback, translations) => {
  swal({
    dangerMode: true,
    icon: 'warning',
    title: xss(translations.areYouSure),
    text: xss(translations.areYouSureYouWantToRefund),
    buttons: {
      cancel: xss(translations.cancel),
      confirm: xss(translations.refund),
    },
  }).then((value) => {
    [].slice.call(document.querySelectorAll('.swal-overlay')).forEach((elem) => {
      elem.parentNode.removeChild(elem);
    });

    callback(!!value);
  });
};

export default refund;
