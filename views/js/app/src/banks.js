import 'babel-polyfill';
import 'classlist-polyfill';
import 'raf/polyfill';
import swal from 'sweetalert';
import xss from 'xss';
import styles from '../css/banks.css';

export default class MollieBanks {
  constructor(banks, translations) {
    this.banks = banks;
    this.translations = translations;

    this.initBanks();
    this.initQrImage();

    window.addEventListener('resize', this.constructor.throttle(() => {
      console.log('bnlark');
      this.constructor.checkWindowSize();
    }, 200))
  }

  static throttle(callback, delay) {
    let isThrottled = false, args, context;

    function wrapper() {
      if (isThrottled) {
        args = arguments;
        context = this;
        return;
      }

      isThrottled = true;
      callback.apply(this, arguments);

      setTimeout(() => {
        isThrottled = false;
        if (args) {
          wrapper.apply(context, args);
          args = context = null;
        }
      }, delay);
    }

    return wrapper;
  }

  static checkWindowSize() {
    const elem = document.getElementById('mollie-qr-code');
    if (elem) {
      if (window.innerWidth > 800 && window.innerHeight > 860) {
        elem.style.display = 'block';
      } else {
        elem.style.display = 'none';
      }
    }
  }

  static pollStatus = (idTransaction) => {
    setTimeout(() => {
      let request = new XMLHttpRequest();
      request.open('GET', window.MollieModule.urls.qrCodeStatus + '&transaction_id=' + idTransaction, true);

      request.onreadystatechange = function() {
        if (this.readyState === 4) {
          if (this.status >= 200 && this.status < 400) {
            // Success!
            try {
              const data = JSON.parse(this.responseText);
              if (data.status) {
                // Never redirect to a different domain
                const a = document.createElement('A');
                a.href = data.href;
                if (a.hostname === window.location.hostname) {
                  window.location.href = data.href;
                }
              } else {
                MollieBanks.pollStatus(idTransaction);
              }
            } catch (e) {
              MollieBanks.pollStatus(idTransaction);
            }
          } else {
            MollieBanks.pollStatus(idTransaction);
          }
        }
      };

      request.send();
      request = null;
    }, 5000);
  };

  initBanks() {
    const elem = document.createElement('div');
    elem.id = 'mollie-banks-list';
    let content = '<ul>';
    Object.values(this.banks).forEach((bank) => {
      content += `<div class="${styles.radio} ${styles['radio-primary']}">
            <input type="radio" id="${xss(bank.id)}" name="mollie-bank" value="${xss(bank.id)}">
            <label for="${xss(bank.id)}">
                <img src="${xss(bank.image.size2x)}" alt="${xss(bank.image.size2x)}"> ${xss(bank.name)}
            </label>
        </div>`;
    });
    content += '</ul>';
    content += `<div id="mollie-qr-code" style="width: 100%; height: 280px; align-content: center; text-align: center">
  <div id="mollie-spinner" class="${styles.spinner}" style="height: 100px">
    <div class="${styles.bounce1}"></div>
    <div class="${styles.bounce2}"></div>
    <div></div>
  </div>
  <div id="mollie-qr-image-container" style="text-align: center">
    <span id="mollie-qr-title" style="font-size: 20px">${xss(this.translations.orPayByIdealQr)}</span>
    <img id="mollie-qr-image" width="320" height="320" style="height: 240px; width: 240px; margin: 0 auto; visibility: hidden">
  </div>
</div>`;
    elem.innerHTML = content;
    elem.querySelector('input').checked = true;

    swal({
      title: xss(this.translations.chooseYourBank),
      content: elem,
      buttons: {
        cancel: xss(this.translations.cancel),
        confirm: xss(this.translations.choose),
      },
    }).then((value) => {
      if (value) {
        const issuer = elem.querySelector('input[name="mollie-bank"]:checked').value;

        var win = window.open(this.banks[issuer].href, '_self');
        win.opener = null;
      } else {
        [].slice.call(document.querySelectorAll('.swal-overlay')).forEach((item) => {
          item.parentNode.removeChild(item);
        });
      }
    });
  }

  initQrImage() {
    const elem = document.getElementById('mollie-qr-image');
    const self = this;
    elem.style.display = 'none';
    let request = new XMLHttpRequest();
    request.open('GET', window.MollieModule.urls.qrCodeNew, true);

    request.onreadystatechange = function() {
      if (this.readyState === 4) {
        if (this.status >= 200 && this.status < 400) {
          // Success!
          try {
            const data = JSON.parse(this.responseText);
            // Preload an image and check if it loads, if not, hide the qr block
            const img = new Image();
            img.onload = () => {
              if (img.src && img.width) {
                elem.src = data.href;
                elem.style.display = 'block';
                document.getElementById('mollie-spinner').style.display = 'none';
                document.getElementById('mollie-qr-image').style.visibility = 'visible';
                self.constructor.pollStatus(data.idTransaction);
              } else {
                document.getElementById('mollie-qr-code').style.display = 'none';
              }
            };
            img.onerror = () => {
              document.getElementById('mollie-qr-code').style.display = 'none';
            };
            img.src = data.href;
          } catch (e) {
          }
        } else {
          // Error :(
        }
      }
    };

    request.send();
    request = null;
  };
}
