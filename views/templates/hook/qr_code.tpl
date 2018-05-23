<div id="mollie-qr-code" style="width: 100%; height: 280px; align-content: center; text-align: center">
  <div id="mollie-spinner" class="spinner" style="height: 100px">
    <div class="bounce1"></div>
    <div class="bounce2"></div>
    <div class="bounce3"></div>
  </div>

<div id="mollie-qr-image-container" style="text-align: center">
  <span id="mollie-qr-title" style="font-size: 20px">{l s='or scan the iDEAL QR code' mod='mollie'}</span>
  <img id="mollie-qr-image" width="320" height="320" style="height: 240px; width: 240px; margin: 0 auto; visibility: hidden">
</div>

<script type="text/javascript">
  (function () {
    function pollStatus(idTransaction) {
      setTimeout(function () {
        var request = new XMLHttpRequest();
        request.open('GET', '{$link->getModuleLink('mollie', 'qrcode', ['ajax' => '1', 'action' => 'qrCodeStatus'], Tools::usingSecureMode())|escape:'javscript':'UTF-8'}' + '&transaction_id=' + idTransaction, true);

        request.onreadystatechange = function () {
          if (this.readyState === 4) {
            if (this.status >= 200 && this.status < 400) {
              // Success!
              try {
                var data = JSON.parse(this.responseText);
                if (data.status) {
                  // Never redirect to a different domain
                  var a = document.createElement('A');
                  a.href = data.href;
                  if (a.hostname === window.location.hostname) {
                    window.location.href = data.href;
                  }
                } else {
                  pollStatus(idTransaction);
                }
              } catch (e) {
                pollStatus(idTransaction);
              }
            } else {
              pollStatus(idTransaction);
            }
          }
        };

        request.send();
        request = null;
      }, 5000);
    };

    function initQrImage() {
      var elem = document.getElementById('mollie-qr-image');
      var self = this;
      elem.style.display = 'none';
      var request = new XMLHttpRequest();
      request.open('GET', '{$link->getModuleLink('mollie', 'qrcode', ['ajax' => '1', 'action' => 'qrCodeNew'])|escape:'javascript':'UTF-8'}', true);

      request.onreadystatechange = function() {
        if (this.readyState === 4) {
          if (this.status >= 200 && this.status < 400) {
            // Success!
            try {
              var data = JSON.parse(this.responseText);
              // Preload an image and check if it loads, if not, hide the qr block
              var img = new Image();
              img.onload = () => {
                if (img.src && img.width) {
                  elem.src = data.href;
                  elem.style.display = 'block';
                  document.getElementById('mollie-spinner').style.display = 'none';
                  document.getElementById('mollie-qr-image').style.visibility = 'visible';
                  pollStatus(data.idTransaction);
                } else {
                  document.getElementById('mollie-qr-code').style.display = 'none';
                }
              };
              img.onerror = function () {
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

    initQrImage();
  }());
</script>
