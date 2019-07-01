{**
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
    var request = new XMLHttpRequest();
    request.open('GET', '{$checkStatusEndpoint|escape:'javascript':'UTF-8'}', true);

    request.onload = function() {
      if (request.status >= 200 && request.status < 400) {
        try {
          var data = JSON.parse(request.responseText);
          if (data.success && Number(data.status) === 2) {
            window.location.href = data.href;
            return;
          }
        } catch (e) {
        }
      }

      setTimeout(awaitMolliePaymentStatus, timeout);
    };

    request.onerror = function() {
      setTimeout(awaitMolliePaymentStatus, timeout);
    };

    request.send();
  }());
</script>

