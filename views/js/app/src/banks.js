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
    if (window.mollieQrEnabled) {
      this.grabAmount().then(this.initQrImage);
      window.addEventListener('resize', this.constructor.throttle(() => {
        this.constructor.checkWindowSize();
      }, 200));
    }
  }

  initBanks = () => {
    const elem = document.createElement('div');
    elem.id = 'mollie-banks-list';
    let content = '<ul>';
    Object.values(this.banks).forEach((bank) => {
      content += `<div class="${styles.radio} ${styles['radio-primary']}">
            <input type="radio" id="${xss(bank.id)}" name="mollie-bank" value="${xss(bank.id)}">
            <label for="${xss(bank.id)}" style="line-height: 24px;">
                <img src="${xss(bank.image.size2x)}" alt="${xss(bank.image.size2x)}" style="height: 24px; width: auto"> ${xss(bank.name)}
            </label>
        </div>`;
    });
    content += '</ul>';
    if (window.mollieQrEnabled) {
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
    }
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
        const win = window.open(this.banks[issuer].href, '_self');
        win.opener = null;
      } else {
        [].slice.call(document.querySelectorAll('.swal-overlay')).forEach((item) => {
          item.parentNode.removeChild(item);
        });
      }
    });
  };

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

  static clearCache = () => {
    Object.keys(window.localStorage).forEach((key) => {
      if (key.indexOf('mollieqrcache') > -1) {
        window.localStorage.removeItem(key);
      }
    });
  };

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

  pollStatus = (idTransaction) => {
    const self = this;

    setTimeout(() => {
      let request = new XMLHttpRequest();
      request.open('GET', window.MollieModule.urls.qrCodeStatus + '&transaction_id=' + idTransaction, true);

      request.onreadystatechange = () => {
        if (request.readyState === 4) {
          if (request.status >= 200 && request.status < 400) {
            // Success!
            try {
              const data = JSON.parse(request.responseText);
              if (parseInt(data.status, 10) === 2) {
                MollieBanks.clearCache();

                // Never redirect to a different domain
                const a = document.createElement('A');
                a.href = data.href;
                if (a.hostname === window.location.hostname) {
                  window.location.href = data.href;
                }
              } else if (parseInt(data.status, 10) === 3) {
                MollieBanks.clearCache();
                self.grabNewUrl();
              } else {
                self.pollStatus(idTransaction);
              }
            } catch (e) {
              self.pollStatus(idTransaction);
            }
          } else {
            self.pollStatus(idTransaction);
          }
          request = null;
        }
      };

      request.send();
    }, 5000);
  };

  grabAmount = () => {
    return new Promise((resolve) => {
      let request = new XMLHttpRequest();
      request.open('GET', window.MollieModule.urls.cartAmount, true);

      request.onreadystatechange = () => {
        if (request.readyState === 4) {
          if (request.status >= 200 && request.status < 400) {
            // Success!
            try {
              const data = JSON.parse(request.responseText);
              resolve(data.amount);
            } catch (e) {
              console.log(JSON.stringify(e));
            }
          } else {
            console.log(JSON.stringify(e));
          }

          request = null;
        }
      };

      request.send();
    });
  };

  grabNewUrl = () => {
    const self = this;
    let request = new XMLHttpRequest();
    request.open('GET', window.MollieModule.urls.qrCodeNew, true);

    request.onreadystatechange = () => {
      if (request.readyState === 4) {
        if (request.status >= 200 && request.status < 400) {
          // Success!
          try {
            const data = JSON.parse(request.responseText);
            window.localStorage.setItem('mollieqrcache-' + data.expires + '-' + data.amount, JSON.stringify({
              url: data.href,
              idTransaction: data.idTransaction,
            }));
            // Preload an image and check if it loads, if not, hide the qr block
            const img = new Image();
            img.onload = () => {
              if (img.src && img.width) {
                const elem = document.getElementById('mollie-qr-image');
                elem.src = data.href;
                elem.style.display = 'block';
                document.getElementById('mollie-spinner').style.display = 'none';
                document.getElementById('mollie-qr-image').style.visibility = 'visible';
                self.pollStatus(data.idTransaction);
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
        request = null;
      }
    };

    request.send();
  };

  initQrImage = (amount) => {
    const elem = document.getElementById('mollie-qr-image');
    const self = this;
    elem.style.display = 'none';

    let url = null;
    let idTransaction = null;
    if (typeof window.localStorage !== 'undefined') {
      Object.keys(window.localStorage).forEach((key) => {
        if (key.indexOf('mollieqrcache') > -1) {
          const cacheInfo = key.split('-');
          if (cacheInfo[1] > (+ new Date() + 60 * 1000) && parseInt(cacheInfo[2], 10) === amount) {
            const item = JSON.parse(window.localStorage.getItem(key));
            const a = document.createElement('A');
            a.href = item.url;
            if (!/\.ideal\.nl$/i.test(a.hostname) || a.protocol !== 'https:') {
              window.localStorage.removeItem(key);
              return;
            }
            // Valid
            url = item.url;
            idTransaction = item.idTransaction;
            return false;
          } else {
            window.localStorage.removeItem(key);
          }
        }
      });
      if (url && idTransaction) {
        const img = new Image();
        img.onload = () => {
          if (img.src && img.width) {
            elem.src = url;
            elem.style.display = 'block';
            document.getElementById('mollie-spinner').style.display = 'none';
            document.getElementById('mollie-qr-image').style.visibility = 'visible';
            self.pollStatus(idTransaction);
          } else {
            document.getElementById('mollie-qr-code').style.display = 'none';
          }
        };
        img.onerror = () => {
          document.getElementById('mollie-qr-code').style.display = 'none';
        };
        img.src = url;
      } else {
        self.grabNewUrl();
      }
    }
  };
}
