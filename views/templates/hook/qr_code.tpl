{**
* Copyright (c) 2012-2018, Mollie B.V.
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
*}
{if Configuration::get(Mollie::MOLLIE_QRENABLED)}
  <div id="mollie-qr-code" style="width: 100%; height: 280px; align-content: center; text-align: center">
    <div id="mollie-spinner" class="spinner" style="height: 100px">
      <div class="bounce1"></div>
      <div class="bounce2"></div>
      <div class="bounce3"></div>
    </div>

    <div id="mollie-qr-image-container" style="text-align: {if !empty($qrAlign)}{$qrAlign|escape:'html':'UTF-8'}{else}center{/if}">
      <span id="mollie-qr-title" style="font-size: 20px">{l s='or scan the iDEAL QR code' mod='mollie'}</span>
      <img id="mollie-qr-image"
           width="320"
           height="320"
           style="height: 240px; width: 240px; visibility: hidden;{if !empty($qrAlign) && $qrAlign === 'center'} margin: 0 auto;{/if}"
      >
    </div>
  </div>
  <script type="text/javascript">
    (function () {
      function throttle(callback, delay) {
        var isThrottled = false, args, context;

        function wrapper() {
          if (isThrottled) {
            args = arguments;
            context = this;
            return;
          }

          isThrottled = true;
          callback.apply(this, arguments);

          setTimeout(function () {
            isThrottled = false;
            if (args) {
              wrapper.apply(context, args);
              args = context = null;
            }
          }, delay);
        }

        return wrapper;
      }

      function checkWindowSize() {
        var elem = document.getElementById('mollie-qr-code');
        if (elem) {
          if (window.innerWidth > 800 && window.innerHeight > 860) {
            elem.style.display = 'block';
          } else {
            elem.style.display = 'none';
          }
        }
      }

      function clearCache() {
        Object.keys(window.localStorage).forEach(function (key) {
          if (key.indexOf('mollieqrcache') > -1) {
            window.localStorage.removeItem(key);
          }
        });
      }

      function pollStatus(idTransaction) {
        setTimeout(function () {
          var request = new XMLHttpRequest();
          request.open('GET', '{$link->getModuleLink('mollie', 'qrcode', ['ajax' => '1', 'action' => 'qrCodeStatus'], Tools::usingSecureMode())|escape:'javascript':'UTF-8' nofilter}' + '&transaction_id=' + idTransaction, true);

          request.onreadystatechange = function () {
            if (this.readyState === 4) {
              if (this.status >= 200 && this.status < 400) {
                // Success!
                try {
                  var data = JSON.parse(this.responseText);
                  if (data.status === 2) {
                    clearCache();
                    // Never redirect to a different domain
                    var a = document.createElement('A');
                    a.href = data.href;
                    if (a.hostname === window.location.hostname) {
                      window.location.href = data.href;
                    }
                  } else if (data.status === 3) {
                    clearCache();
                    grabNewUrl();
                  }
                  else {
                    pollStatus(idTransaction);
                  }
                } catch (e) {
                  pollStatus(idTransaction);
                }
              } else {
                pollStatus(idTransaction);
              }
              request = null;
            }
          };

          request.send();
        }, 5000);
      }

      function setImage(url, resolve, reject) {
        var img = new Image();
        img.onload = function () {
          if (img.src && img.width) {
            var elem = document.getElementById('mollie-qr-image');
            elem.src = url;
            elem.style.display = 'block';
            document.getElementById('mollie-spinner').style.display = 'none';
            document.getElementById('mollie-qr-image').style.visibility = 'visible';
            if (typeof resolve === 'function') {
              resolve();
            }
          } else {
            document.getElementById('mollie-qr-code').style.display = 'none';
            if (typeof reject === 'function') {
              reject();
            }
          }
        };
        img.onerror = function () {
          document.getElementById('mollie-qr-code').style.display = 'none';
          if (typeof reject === 'function') {
            reject();
          }
        };
        img.src = url;
      }

      function grabNewUrl() {
        var request = new XMLHttpRequest();
        request.open('GET', '{$link->getModuleLink('mollie', 'qrcode', ['ajax' => '1', 'action' => 'qrCodeNew'])|escape:'javascript':'UTF-8' nofilter}', true);

        request.onreadystatechange = function () {
          if (this.readyState === 4) {
            if (this.status >= 200 && this.status < 400) {
              // Success!
              try {
                var data = JSON.parse(this.responseText);
                if (data.href && data.idTransaction && data.expires) {
                  window.localStorage.setItem('mollieqrcache-' + data.expires + '-{$cartAmount|intval}', JSON.stringify({
                    url: data.href,
                    idTransaction: data.idTransaction
                  }));

                  setImage(data.href, function () {
                    pollStatus(data.idTransaction);
                  });
                } else {
                  document.getElementById('mollie-qr-code').style.display = 'none';
                }
              } catch (e) {
              }
            } else {
            }
            request = null;
          }
        };

        request.send();
      }

      function initQrImage() {
        var elem = document.getElementById('mollie-qr-image');
        elem.style.display = 'none';

        window.addEventListener('resize', throttle(function () {
          checkWindowSize();
        }, 200));

        var url = null;
        var idTransaction = null;
        if (typeof window.localStorage !== 'undefined') {
          Object.keys(window.localStorage).forEach(function (key) {
            if (key.indexOf('mollieqrcache') > -1) {
              var cacheInfo = window.localStorage[key].split('-');
              if (cacheInfo[1] > (+new Date() + 60 * 1000) && parseInt(cacheInfo[2], 10) === {$cartAmount|intval}) {
                var item = JSON.parse(window.localStorage.getItem(key));
                var a = document.createElement('A');
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
            setImage(url, function () {
              pollStatus(idTransaction);
            });
          } else {
            grabNewUrl();
          }
        }
      }

      if (typeof window.IntersectionObserver !== 'undefined') {
        var observer = new IntersectionObserver(function (changes) {
          changes.forEach(function (change) {
            if (change.intersectionRatio > 0) {
              initQrImage();
              observer.disconnect();
            }
          });
        }, {
          root: null,
          rootMargin: '0px',
          threshold: 0.5
        });
        observer.observe(document.getElementById('mollie-qr-code'));
      } else {
        var interval = setInterval(function () {
          if (document.getElementById('mollie-qr-code').offsetParent) {
            initQrImage();
            clearInterval(interval);
          }
        }, 500);
      }
    }());
  </script>
{/if}
