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
<div class="form-group js-api-test-results" data-tab-id="general_settings">
    <label class="control-label col-lg-3">
    </label>

    <div class="col-lg-9">
        {if $testKeyInfo.status}
            <div>
                <p>
                    <i class="icon-check text-success"></i>
                    {l s='Test API-key: Success!' mod='mollie'}
                </p>
            </div>
            <div class="clearfix"></div>
            <div>
                <p>
                    <i class="icon-gear text-success"></i>
                    <strong>{l s='Enabled Methods: ' mod='mollie'}</strong>
                    {', '|implode:$testKeyInfo.methods}

                </p>
            </div>
        {else}
            <div>
                <p>
                    <i class="icon-remove text-danger"></i>
                    {l s='Test API-key: Failed!' mod='mollie'}
                </p>
            </div>
        {/if}
    </div>
</div>

<div class="form-group js-api-test-results" data-tab-id="general_settings">
    <label class="control-label col-lg-3">
    </label>

    <div class="col-lg-9">
        {if $liveKeyInfo.status}
            <div>
                <p>
                    <i class="icon-check text-success"></i>
                    {l s='Live API-key: Success!' mod='mollie'}
                </p>
            </div>
            <div class="clearfix"></div>
            <div>
                <p>
                    <i class="icon-gear text-success"></i>
                    <strong>{l s='Enabled Methods: ' mod='mollie'}</strong>
                    {', '|implode:$liveKeyInfo.methods}

                </p>
            </div>
        {else}
            <div>
                <p>
                    <i class="icon-remove text-danger"></i>
                    {l s='Live API-key: Failed!' mod='mollie'}
                </p>
            </div>
        {/if}
    </div>
</div>


