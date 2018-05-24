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
                        text: '{l s='Unable to unzip new smodule' mod='mollie' js=1}'
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
