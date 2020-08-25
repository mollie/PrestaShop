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


