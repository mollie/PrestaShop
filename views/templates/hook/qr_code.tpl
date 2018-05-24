<div id="mollie-qr-code" style="width: 100%; height: 280px; align-content: center; text-align: center">
  <div id="mollie-spinner" class="spinner" style="height: 100px">
    <div class="bounce1"></div>
    <div class="bounce2"></div>
    <div class="bounce3"></div>
  </div>

  <div id="mollie-qr-image-container" style="text-align: {if !empty($qrAlign)}{$qrAlign}{else}center{/if}">
    <span id="mollie-qr-title" style="font-size: 20px">{l s='or scan the iDEAL QR code' mod='mollie'}</span>
    <img id="mollie-qr-image" width="320" height="320" style="height: 240px; width: 240px; visibility: hidden;{if !empty($qrAlign) && $qrAlign === 'center'} margin: 0 auto;{/if}">
  </div>
</div>

<script type="text/javascript">
  (function () {
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
                if (parseInt(data.status, 10) === 2) {
                  clearCache();
                  // Never redirect to a different domain
                  var a = document.createElement('A');
                  a.href = data.href;
                  if (a.hostname === window.location.hostname) {
                    window.location.href = data.href;
                  }
                } else if (parseInt(data.status, 10) === 3) {
                  clearCache();
                  initQrImage();
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

      request.onreadystatechange = function() {
        if (this.readyState === 4) {
          if (this.status >= 200 && this.status < 400) {
            // Success!
            try {
              var data = JSON.parse(this.responseText);
              if (data.href && data.idTransaction && data.expires) {
                window.localStorage.setItem('mollieqrcache-' + data.expires + '-{$cartAmount|intval}', JSON.stringify({
                  url: data.href,
                  idTransaction: data.idTransaction,
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

      var url = null;
      var idTransaction = null;
      if (typeof window.localStorage !== 'undefined') {
        Object.keys(window.localStorage).forEach(function (key) {
          if (key.indexOf('mollieqrcache') > -1) {
            var cacheInfo = window.localStorage[key].split('-');
            if (cacheInfo[1] > (+ new Date() + 60 * 1000) && cacheInfo[2] == {$cartAmount|intval}) {
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
          }
        });
      }, {
        root: null,
        rootMargin: '0px',
        threshold: 0.5,
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
