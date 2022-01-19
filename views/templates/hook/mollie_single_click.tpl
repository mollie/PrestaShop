<div class="mollie-single-click-container">
    {if $mollieUseSavedCard}
        <div class="mollie-use-saved-card">
            <div class="form-group form-group-use-saved-card mt-1">
                <label class="mollie-label"
                       for="mollie-use-saved-card">{l s='Use saved card' mod='mollie'}</label>
                <div class="save-card">
                    <input type="checkbox" name="mollie-use-saved-card" id="mollie-use-saved-card" checked>
                </div>
            </div>
        </div>
    {else}
        <div class="form-group form-group-save-card mb-1">
            <label class="mollie-label"
                   for="mollie-save-card">{l s='Save card' mod='mollie'}</label>
            <div class="save-card">
                <input type="checkbox" name="mollie-save-card" id="mollie-save-card">
            </div>
        </div>
    {/if}
</div>
