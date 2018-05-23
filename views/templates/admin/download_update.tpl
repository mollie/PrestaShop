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
                          jAlert('{l s='The module has been updated!' mod='mollie' js=1}');
                        } else if (data.errors) {
                          var message = 'Error: ';
                          data.errors.forEach(function (error) {
                            message +=  '\n' + error;
                          });
                          jAlert('Error: ' + message);
                        }
                      } else {
                        jAlert('Error: unable to connect');
                      }
                    }
                  };

                  unzipRequest.send();
                  unzipRequest = null;
                } else if (data.errors) {
                  var message = 'Error: ';
                  data.errors.forEach(function (error) {
                    message +=  '\n' + error;
                  });
                  jAlert('Error: ' + message);
                }
            } else {
              jAlert('Error: unable to connect');
            }
          }
        };

        downloadRequest.send();
        downloadRequest = null;


      }
    }
  }());
</script>
