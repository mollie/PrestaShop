{**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 *
 * @see        https://github.com/mollie/PrestaShop
 * @codingStandardsIgnoreStart
 *}
<h2>{l s='Awaiting payment status' mod='mollie'}</h2>
<div class="mollie-spinner">
  <div class="rect1"></div>
  <div class="rect2"></div>
  <div class="rect3"></div>
  <div class="rect4"></div>
  <div class="rect5"></div>
</div>
<style>
  .mollie-spinner {
    margin:     100px auto;
    width:      50px;
    height:     40px;
    text-align: center;
    font-size:  10px;
  }

  .mollie-spinner > div {
    background-color:  #333;
    height:            100%;
    width:             6px;
    display:           inline-block;

    -webkit-animation: sk-stretchdelay 1.2s infinite ease-in-out;
    animation:         sk-stretchdelay 1.2s infinite ease-in-out;
  }

  .mollie-spinner .rect2 {
    -webkit-animation-delay: -1.1s;
    animation-delay:         -1.1s;
  }

  .mollie-spinner .rect3 {
    -webkit-animation-delay: -1.0s;
    animation-delay:         -1.0s;
  }

  .mollie-spinner .rect4 {
    -webkit-animation-delay: -0.9s;
    animation-delay:         -0.9s;
  }

  .mollie-spinner .rect5 {
    -webkit-animation-delay: -0.8s;
    animation-delay:         -0.8s;
  }

  @-webkit-keyframes sk-stretchdelay {
    0%, 40%, 100% {
      -webkit-transform: scaleY(0.4)
    }
    20% {
      -webkit-transform: scaleY(1.0)
    }
  }

  @keyframes sk-stretchdelay {
    0%, 40%, 100% {
      transform:         scaleY(0.4);
      -webkit-transform: scaleY(0.4);
    }
    20% {
      transform:         scaleY(1.0);
      -webkit-transform: scaleY(1.0);
    }
  }
</style>
<script type="text/javascript">
  (function awaitMolliePaymentStatus() {
    var timeout = 3000;
    var retryLimit = 5;
    var retryCount = 0;

    function checkPaymentStatus() {
      var request = new XMLHttpRequest();
      // nofilter is needed for URL with variables
      request.open('GET', '{$checkStatusEndpoint|escape:"javascript":"UTF-8" nofilter}', true);

      request.onload = function() {
        if (request.status >= 200 && request.status < 400) {
          try {
            var data = JSON.parse(request.responseText);
            if (data.success && Number(data.status) === 2) {
              window.location.href = data.href;
              return;
            }
          } catch (e) {
            console.error("Error parsing response", e);
          }
        }

        if (retryCount < retryLimit) {
          retryCount++;
          console.log(retryCount);
          setTimeout(checkPaymentStatus, timeout);
        } else {
          // same link just add failed=1 argument to link
          var url = new URL(window.location.href);
          url.searchParams.set('failed', 1);
          // redirect
          window.location.href = url.href;
        }
      };

      request.onerror = function() {
        // Retry on error as long as we haven't reached the limit
        if (retryCount < retryLimit) {
          retryCount++;
          console.log(retryCount);
          setTimeout(checkPaymentStatus, timeout);
        } else {
          window.location.href = '/';
        }
      };

      request.send();
    }

    // Start the request
    checkPaymentStatus();
  }());
</script>


