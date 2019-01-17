/**
 * Copyright (c) 2012-2019, Mollie B.V.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * - Redistributions of source code must retain the above copyright notice,
 *    this list of conditions and the following disclaimer.
 * - Redistributions in binary form must reproduce the above copyright
 *    notice, this list of conditions and the following disclaimer in the
 *    documentation and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE AUTHOR AND CONTRIBUTORS ``AS IS'' AND ANY
 * EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE AUTHOR OR CONTRIBUTORS BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY
 * OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH
 * DAMAGE.
 *
 * @author     Mollie B.V. <info@mollie.nl>
 * @copyright  Mollie B.V.
 * @license    Berkeley Software Distribution License (BSD-License 2) http://www.opensource.org/licenses/bsd-license.php
 * @category   Mollie
 * @package    Mollie
 * @link       https://www.mollie.nl
 */
import '@babel/polyfill';
import React from 'react';
import ReactDOM from 'react-dom';

import swal from 'sweetalert';
import xss from 'xss';
import { IBanks, ITranslations } from '../../globals';


declare let window: any;

export default function (banks: IBanks, translations: ITranslations) {
  const state = {
    issuer: '',
    setIssuer: (newIssuer: string) => {
      this.issuer = newIssuer;
    },
  };

  const wrapper = document.createElement('DIV');
  ReactDOM.render(<Banks/>, wrapper);
  const elem = wrapper.firstChild;


  swal({
    title: xss(this.translations.chooseYourBank),
    content: elem,
    buttons: {
      cancel: xss(this.translations.cancel),
      confirm: xss(this.translations.choose),
    },
  }).then((value: any) => {
    if (value) {
      const win = window.open(this.banks[state.issuer].href, '_self');
      win.opener = null;
    } else {
      [].slice.call(document.querySelectorAll('.swal-overlay')).forEach((item: HTMLElement) => {
        item.parentElement.removeChild(item);
      });
    }
  });
}

