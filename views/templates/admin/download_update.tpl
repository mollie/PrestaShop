{**
* Copyright (c) 2012-2018, mollie-ui b.V.
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
* @author     mollie-ui b.V. <info@mollie.nl>
* @copyright  mollie-ui b.V.
* @license    Berkeley Software Distribution License (BSD-License 2) http://www.opensource.org/licenses/bsd-license.php
* @category   Mollie
* @package    Mollie
* @link       https://www.mollie.nl
*}
<button id="mollie-update" type="button" class="btn btn-primary pull-right"><i class="icon icon-cloud-download"></i> {l s='Update this module' mod='mollie'}</button>
<script type="text/javascript">
  (function () {
    var button = document.getElementById('mollie-update');
    if (button) {
      button.onclick = function (e) {
        var downloadRequest = new XMLHttpRequest();
        downloadRequest.open('GET', '{$link->getAdminLink('AdminModules', true)|escape:'javascript':'UTF-8' nofilter}&configure=mollie&module_name=mollie' + '&ajax=1&action=downloadUpdate', true);

        downloadRequest.onreadystatechange = function () {
          if (this.readyState === 4) {
            if (this.status >= 200 && this.status < 400) {
              // Success!
              var data = JSON.parse(this.responseText);
              if (data.success) {
                var unzipRequest = new XMLHttpRequest();
                unzipRequest.open('GET', '{$link->getAdminLink('AdminModules', true)|escape:'javascript':'UTF-8' nofilter}&configure=mollie&module_name=mollie' + '&ajax=1&action=installUpdate', true);

                unzipRequest.onreadystatechange = function () {
                  if (this.readyState === 4) {
                    if (this.status >= 200 && this.status < 400) {
                      // Success!
                      var data = JSON.parse(this.responseText);
                      if (data.success) {
                        var upgradeRequest = new XMLHttpRequest();
                        upgradeRequest.open('GET', '{$link->getAdminLink('AdminModules', true)|escape:'javascript':'UTF-8' nofilter}&configure=mollie&module_name=mollie' + '&ajax=1&action=runUpgrade', true);

                        upgradeRequest.onreadystatechange = function () {
                          if (this.readyState === 4) {
                            if (this.status >= 200 && this.status < 400) {
                              // Success!
                              var data = JSON.parse(this.responseText);
                              if (data.success) {
                                swal({
                                  icon: 'success',
                                  text: '{l s='The module has been updated!' mod='mollie' js=1}'
                                });
                              } else if (data.message) {
                                swal({
                                  icon: 'error',
                                  title: '{l s='Error' mod='mollie' js=1}',
                                  text: 'error' + data.message
                                });
                              }
                            } else {
                              swal({
                                icon: 'error',
                                title: '{l s='Error' mod='mollie' js=1}',
                                text: '{l s='Unable to connect' mod='mollie' js=1}',
                              });
                            }
                            upgradeRequest = null;
                          }
                        };

                        upgradeRequest.send();
                      } else if (data.message) {
                        swal({
                          icon: 'error',
                          title: '{l s='Error' mod='mollie' js=1}',
                          text: 'error' + data.message
                        });
                      } else {
                        swal({
                          icon: 'error',
                          title: 'Error',
                          text: '{l s='Unable to unzip new module' mod='mollie' js=1}'
                        });
                      }
                    } else {
                      swal({
                        icon: 'error',
                        title: 'Error',
                        text: '{l s='Unable to unzip new module' mod='mollie' js=1}'
                      });
                    }
                    unzipRequest = null;
                  }
                };

                unzipRequest.send();
              } else if (data.message) {
                swal({
                  icon: 'error',
                  title: '{l s='Error' mod='mollie' js=1}',
                  text: data.message
                });
              } else {
                swal({
                  icon: 'error',
                  title: '{l s='Error' mod='mollie' js=1}',
                  text: '{l s='Unable to connect' mod='mollie' js=1}'
                });
              }
            } else {
              swal({
                icon: 'error',
                title: '{l s='Error' mod='mollie' js=1}',
                text: '{l s='Unable to connect' mod='mollie' js=1}'
              });
            }
            downloadRequest = null;
          }
        };
        downloadRequest.send();
      }
    }
  }());
</script>
