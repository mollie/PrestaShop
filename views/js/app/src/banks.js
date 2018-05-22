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
  }

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

        console.log(this.banks[issuer]);

        window.open(this.banks[issuer].href, '_self');
      } else {
        [].slice.call(document.querySelectorAll('.swal-overlay')).forEach((item) => {
          item.parentNode.removeChild(item);
        });
      }
    });
  }
}
