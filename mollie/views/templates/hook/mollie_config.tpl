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

<form action="{$form_action}" method="post" class="Mollie_Config">
<h2>{$config_title}</h2>
    <fieldset>
        <legend>Mollie Settings</legend>
        <table>
            {if !empty($msg_result)}
                <tr class="Mollie_Result_Msg">
                    <td colspan="2">
                        <span id="Mollie_Result_Msg">{$msg_result}</span>
                    </td>
                </tr>
            {/if}
            <tr>
                <td class="Mollie_Msg">
                    <label for="Mollie_Api_Key"><b>{$msg_api_key}</b></label>
                </td>
                <td class="Mollie_Input">
                    <input name="Mollie_Api_Key" id="Mollie_Api_Key" value="{$val_api_key}" /> <br />
                    <label for="Mollie_Api_Key"><i>{$desc_api_key}</i></label>
                </td>
            </tr>
            <tr>
                <td class="Mollie_Msg">
                    <label for="Mollie_Description"><b>{$msg_desc}</b></label>
                </td>
                <td class="Mollie_Input">
                    <input name="Mollie_Description" id="Mollie_Description" value="{$val_desc}" /> <br />
                    <label for="Mollie_Description"><i>{$desc_desc}</i></label>
                </td>
            </tr>
            <tr>
                <td class="Mollie_Msg">
                    <label for="Mollie_Images"><b>{$msg_images}</b></label>
                </td>
                <td class="Mollie_Input">
                    <select name="Mollie_Images" id="Mollie_Images">
                        {foreach $image_options as $option}
                            <option value="{$option}" {if $option == $val_images}selected="selected"{/if}>{$option}</option>
                        {/foreach}
                    </select><br />
                    <label for="Mollie_Images"><i>{$desc_images}</i></label>
                </td>
            </tr>
            {foreach $statuses as $i => $name}
            <tr>
                <td class="Mollie_Msg">
                    <label for="Mollie_Status_{$name}"><b>{$msg_status_{$name}}</b></label>
                </td>
                <td class="Mollie_Input">
                    <select name="Mollie_Status_{$name}" id="Mollie_Status_{$name}">
                        {foreach $all_statuses as $j => $status}
                            {if $status['id_order_state'] == {$val_status_{$name}}}
                                <option style="background-color:{$status['color']}; color:white; border:3px solid black;" value="{$status['id_order_state']}" selected="selected">{$status['name']} ({$status['id_order_state']})</option>
                            {else}
                                <option style="background-color:{$status['color']}; color:white;" value="{$status['id_order_state']}">{$status['name']} ({$status['id_order_state']})</option>
                            {/if}
                        {/foreach}
                    </select><br />
                    <label for="Mollie_Status_{$name}"><i>{$desc_status_{$name}}</i></label>
                </td>
            </tr>
            <tr>
                <td class="Mollie_Msg">
                    <label for="Mollie_Mail_When_{$name}"><b>{$msg_mail_{$name}}</b></label>
                </td>
                <td class="Mollie_Input">
                    <input name="Mollie_Mail_When_{$name}" id="Mollie_Mail_When_{$name}" type="checkbox" value="1" {if $val_mail_{$name}}checked="checked"{/if} style="width: auto;" /> <br />
                    <label for="Mollie_Mail_When_{$name}"><i>{$desc_mail_{$name}}</i></label>
                </td>
            </tr>
            {/foreach}
            <tr>
                <td class="Mollie_Msg">
                    {$msg_save}
                </td>
                <td class="Mollie_Input">
                    <input type="submit" name="Mollie_Config_Save" value="{$val_save}" />
                </td>
            </tr>
        </table>
    </fieldset>
</form>