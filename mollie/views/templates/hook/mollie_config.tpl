<style type="text/css">
    .Mollie_Config
    {
        margin-left: auto;
        margin-right: auto;
        width: 660px;
    }
    .Mollie_Config h2
    {
        text-align: center;
    }
    .Mollie_Config label
    {
        text-align: left;
        font-weight: normal;
        width: 100%;
    }
    .Mollie_Config input
    {
        border: 1px solid #CCCED7;
        width: 100%;
    }
    .Mollie_Config input:hover, .Mollie_Config select:hover
    {
        background: #585A69;
        color: #FFFFFF;
    }
    .Mollie_Config option
    {
        background: #FFFFFF;
        color: #585A69;
    }
    tr.Mollie_Result_Msg
    {
        background: #CCCED7;
    }
    tr.Mollie_Result_Msg td
    {
        padding: 10px;
    }
    td.Mollie_Msg
    {
        width: 200px;
    }
    td.Mollie_Input
    {
        width: 450px;
        height: 50px;
    }
</style>

<form action="{$form_action|escape:'url'}" method="post" class="Mollie_Config">
<h2>{$config_title|escape}</h2>
    <fieldset>
        <legend>Mollie Settings</legend>
        <table>
            {if !empty($msg_result)}
                <tr class="Mollie_Result_Msg">
                    <td colspan="2">
                        <span id="Mollie_Result_Msg">{$msg_result|escape}</span>
                    </td>
                </tr>
            {/if}
            <tr>
                <td class="Mollie_Msg">
                    <label for="Mollie_Api_Key"><b>{$msg_api_key|escape}</b></label>
                </td>
                <td class="Mollie_Input">
                    <input name="Mollie_Api_Key" id="Mollie_Api_Key" value="{$val_api_key}" /> <br />
                    <label for="Mollie_Api_Key"><i>{$desc_api_key|escape}</i></label>
                </td>
            </tr>
            <tr>
                <td class="Mollie_Msg">
                    <label for="Mollie_Description"><b>{$msg_desc|escape}</b></label>
                </td>
                <td class="Mollie_Input">
                    <input name="Mollie_Description" id="Mollie_Description" value="{$val_desc}" /> <br />
                    <label for="Mollie_Description"><i>{$desc_desc|escape}</i></label>
                </td>
            </tr>
            <tr>
                <td class="Mollie_Msg">
                    <label for="Mollie_Images"><b>{$msg_images|escape}</b></label>
                </td>
                <td class="Mollie_Input">
                    <select name="Mollie_Images" id="Mollie_Images">
                        {foreach $image_options as $option}
                            <option value="{$option|escape}" {if $option == $val_images}selected="selected"{/if}>{$option|escape}</option>
                        {/foreach}
                    </select><br />
                    <label for="Mollie_Images"><i>{$desc_images|escape}</i></label>
                </td>
            </tr>
            {foreach $statuses as $i => $name}
            <tr>
                <td class="Mollie_Msg">
                    <label for="Mollie_Status_{$name|escape}"><b>{$msg_status_{$name|escape}|escape}</b></label>
                </td>
                <td class="Mollie_Input">
                    <select name="Mollie_Status_{$name|escape}" id="Mollie_Status_{$name|escape}">
                        {foreach $all_statuses as $j => $status}
                            {if $status['id_order_state'] == {$val_status_{$name|escape}}}
                                <option style="background-color:{$status['color']}; color:white; border:3px solid black;" value="{$status['id_order_state']|escape}" selected="selected">{$status['name']|escape} ({$status['id_order_state']|escape})</option>
                            {else}
                                <option style="background-color:{$status['color']}; color:white;" value="{$status['id_order_state']|escape}">{$status['name']|escape} ({$status['id_order_state']|escape})</option>
                            {/if}
                        {/foreach}
                    </select><br />
                    <label for="Mollie_Status_{$name|escape}"><i>{$desc_status_{$name|escape}|escape}</i></label>
                </td>
            </tr>
            <tr>
                <td class="Mollie_Msg">
                    <label for="Mollie_Mail_When_{$name|escape}"><b>{$msg_mail_{$name|escape}|escape}</b></label>
                </td>
                <td class="Mollie_Input">
                    <input name="Mollie_Mail_When_{$name|escape}" id="Mollie_Mail_When_{$name|escape}" type="checkbox" value="1" {if $val_mail_{$name|escape}}checked="checked"{/if} style="width: auto;" /> <br />
                    <label for="Mollie_Mail_When_{$name|escape}"><i>{$desc_mail_{$name|escape}|escape}</i></label>
                </td>
            </tr>
            {/foreach}
            <tr>
                <td class="Mollie_Msg">
                    {$msg_save|escape}
                </td>
                <td class="Mollie_Input">
                    <input type="submit" name="Mollie_Config_Save" value="{$val_save|escape}" />
                </td>
            </tr>
        </table>
    </fieldset>
</form>