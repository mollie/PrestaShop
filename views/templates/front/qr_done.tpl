{**
* Copyright (c) 2012-2020, Mollie B.V.
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
<!doctype html>
<html>
<head>
  <title>{l s='Mollie iDEAL QR' mod='mollie'}</title>
  <style>
    body {
      font-family: Helvetica, Arial, Sans-Serif;
      text-align: center;
    }
    h1 {
      font-size: 1.6em;
    }
    p {
      font-size: 1.2em;
    }
    .ideal-container {
      width: 100%;
    }
    .ideal-logo {
      margin: 0 auto;
    }
  </style>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
  <div class="ideal-container">
    <img class="ideal-logo" src="{$ideal_logo|escape:'htmlall':'UTF-8'}" alt="">
  </div>
  {if !empty($canceled)}
    <h1>{l s='Welcome back' mod='mollie'}</h1>
    <p>{l s='The payment has been canceled.' mod='mollie'}</p>
  {else}
    <h1>{l s='Welcome back' mod='mollie'}</h1>
    <p>{l s='The payment has been completed. Thank you for your order!' mod='mollie'}</p>
  {/if}
</body>
</html>
