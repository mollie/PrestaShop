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

<script type="text/javascript">
  (function () {
    function setInput() {
      var config = [];
      var position = 0;
      $('.sortable > li').each(function (index, elem) {
        var $elem = $(elem);
        config.push({
          id: $elem.attr('data-method'),
          position: position++,
          enabled: $elem.find('input[type=checkbox]').is(':checked'),
        });
      });
      $('#Mollie_Payment_Methods').val(JSON.stringify(config));
    }

    function setPositions() {
      var index = 0;
      $('.sortable > li').each(function (index, elem) {
        var $elem = $(elem);
        $elem.attr('data-pos', index++);
        $elem.find('.positions').text(index);
      });
    }

    function moveUp(event) {
      var $elem = $(event.target).closest('li');
      $elem.prev().insertAfter($elem);
      setPositions();
    }

    function moveDown(event) {

      var $elem = $(event.target).closest('li');
      console.log($elem);
      $elem.next().insertBefore($elem);
      setPositions();
    }

    function init () {
      if (typeof $ === 'undefined') {
        setTimeout(init, 100);
        return;
      }

      $('.sortable').sortable({
        forcePlaceholderSize: true
      }).on('sortupdate', function (event, ui) {
        setPositions();
        setInput();
      });
      $('.sortable > li').each(function (index, elem) {
        var $elem = $(elem);
        $elem.find('a.mollie-up').click(moveUp);
        $elem.find('a.mollie-down').click(moveDown);
        $elem.find('input[type=checkbox]').change(setInput);
      });
      setInput();
    }
    init();
  }());
</script>
