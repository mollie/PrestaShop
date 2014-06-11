<style type="text/css">
    .mollie_config
    {
        margin-left: auto;
        margin-right: auto;
        width: 660px;
    }
    .mollie_config h2
    {
        text-align: center;
    }
    .mollie_config label
    {
        text-align: left;
        font-weight: normal;
        width: 100%;
    }
    .mollie_config input
    {
        border: 1px solid #CCCED7;
        width: 100%;
    }
    .mollie_config input:hover, .mollie_config select:hover
    {
        background: #585A69;
        color: #FFFFFF;
    }
    .mollie_config option
    {
        background: #FFFFFF;
        color: #585A69;
    }
    tr.mollie_update_msg
    {
        background: #585A69;
        color: #FFFFFF;
        font-weight: bold;
    }
    tr.mollie_update_msg td
    {
        padding: 10px;
    }
    tr.mollie_update_msg a
    {
        color: #FFFFFF;
    }
    tr.mollie_update_msg a:hover
    {
        color: #CCCED7;
        text-decoration: underline;
    }
    tr.mollie_result_msg
    {
        background: #CCCED7;
    }
    tr.mollie_result_msg td
    {
        padding: 10px;
    }
    td.mollie_msg
    {
        width: 200px;
        vertical-align: top;
    }
    td.mollie_input
    {
        width: 450px;
        height: 50px;
    }
    .mollie_title
    {
        text-align: center;
        padding-top: 50px;
        padding-bottom: 10px;
    }
</style>

<form action="{$form_action}" method="post" class="mollie_config">
    <h2>{$config_title|escape}</h2>
    <fieldset>
        <legend>{$config_legend|escape}</legend>
        <table>
            {if !empty($update_message)}
                <tr class="mollie_update_msg">
                    <td colspan="2">
                        <span id="mollie_update_msg">{$update_message}</span>
                    </td>
                </tr>
            {/if}
            {if !empty($msg_result)}
                <tr class="mollie_result_msg">
                    <td colspan="2">
                        <span id="mollie_result_msg">{$msg_result}</span>
                    </td>
                </tr>
            {/if}
            <tr>
                <td class="mollie_msg">
                    <label for="Mollie_Api_Key"><b>{$msg_api_key}</b></label>
                </td>
                <td class="mollie_input">
                    <input name="Mollie_Api_Key" id="Mollie_Api_Key" value="{$val_api_key}" /> <br />
                    <label for="Mollie_Api_Key"><i>{$desc_api_key}</i></label>
                </td>
            </tr>
            <tr>
                <td class="mollie_msg">
                    <label for="Mollie_Description"><b>{$msg_desc}</b></label>
                </td>
                <td class="mollie_input">
                    <input name="Mollie_Description" id="Mollie_Description" value="{$val_desc}" /> <br />
                    <label for="Mollie_Description"><i>{$desc_desc}</i></label>
                </td>
            </tr>
            <tr>
                <td colspan="2" class="mollie_title">{$title_visual}</td>
            </tr>
            <tr>
                <td class="mollie_msg">
                    <label for="Mollie_Images"><b>{$msg_images}</b></label>
                </td>
                <td class="mollie_input">
                    <select name="Mollie_Images" id="Mollie_Images">
                        {foreach $image_options AS $value => $title}
                            <option value="{$value|escape}" {if $value == $val_images}selected="selected"{/if}>{$title|escape}</option>
                        {/foreach}
                    </select><br />
                    <label for="Mollie_Images"><i>{$desc_images}</i></label>
                </td>
            </tr>
            <tr>
                <td class="mollie_msg">
                    <label for="Mollie_Issuers"><b>{$msg_issuers}</b></label>
                </td>
                <td class="mollie_input">
                    <select name="Mollie_Issuers" id="Mollie_Issuers">
                        {foreach $issuer_options AS $value => $title}
                            <option value="{$value|escape}" {if $value == $val_issuers}selected="selected"{/if}>{$title|escape}</option>
                        {/foreach}
                    </select><br />
                    <label for="Mollie_Issuers"><i>{$desc_issuers}</i></label>
                </td>
            </tr>
            <tr>
                <td class="mollie_msg">
                    <label for="Mollie_Css"><b>{$msg_css}</b></label>
                </td>
                <td class="mollie_input">
                    <input name="Mollie_Css" id="Mollie_Css" value="{$val_css}" /> <br />
                    <label for="Mollie_Css"><i>{$desc_css}</i></label>
                </td>
            </tr>
            {foreach $statuses as $i => $name}
            <tr>
                <td colspan="2" class="mollie_title">{$title_status|ucfirst|sprintf:$lang[$name]}</td>
            </tr>
            <tr>
                <td class="mollie_msg">
                    <label for="Mollie_Status_{$name|escape}"><b>{$msg_status_{$name|escape}}</b></label>
                </td>
                <td class="mollie_input">
                    <select name="Mollie_Status_{$name|escape}" id="Mollie_Status_{$name|escape}">
                        {foreach $all_statuses as $j => $status}
                            {if $status['id_order_state'] == {$val_status_{$name|escape}}}
                                <option style="background-color:{$status['color']}; color:white; border:3px solid black;" value="{$status['id_order_state']|escape}" selected="selected">{$status['name']|escape} ({$status['id_order_state']|escape})</option>
                            {else}
                                <option style="background-color:{$status['color']}; color:white;" value="{$status['id_order_state']|escape}">{$status['name']|escape} ({$status['id_order_state']|escape})</option>
                            {/if}
                        {/foreach}
                    </select><br />
                    <label for="Mollie_Status_{$name|escape}"><i>{$desc_status_{$name|escape}}</i></label>
                </td>
            </tr>
            {if $name != Mollie_API_Object_Payment::STATUS_OPEN}
                <tr>
                    <td class="mollie_msg">
                        <label for="Mollie_Mail_When_{$name|escape}"><b>{$msg_mail_{$name|escape}}</b></label>
                    </td>
                    <td class="mollie_input">
                        <input name="Mollie_Mail_When_{$name|escape}" id="Mollie_Mail_When_{$name|escape}" type="checkbox" value="1" {if $val_mail_{$name|escape}}checked="checked"{/if} style="width: auto;" /> <br />
                        <label for="Mollie_Mail_When_{$name|escape}"><i>{$desc_mail_{$name|escape}}</i></label>
                    </td>
                </tr>
            {/if}
            {/foreach}
            <tr>
                <td colspan="2" class="mollie_title">{$title_debug}</td>
            </tr>
            <tr>
                <td class="mollie_msg">
                    <label for="Mollie_Errors"><b>{$msg_errors}</b></label>
                </td>
                <td class="mollie_input">
                    <input name="Mollie_Errors" id="Mollie_Errors" type="checkbox" value="1" {if $val_errors}checked="checked"{/if} style="width: auto;" />
                    <label for="Mollie_Errors"><i>{$desc_errors}</i></label>
                </td>
            </tr>
            <tr>
                <td class="mollie_msg">
                    <label for="Mollie_Logger"><b>{$msg_logger}</b></label>
                </td>
                <td class="mollie_input">
                    <select name="Mollie_Logger" id="Mollie_Logger">
                        {foreach $logger_options AS $value => $title}
                            <option value="{$value}" {if $value === $val_logger}selected="selected"{/if}>{$title|escape}</option>
                        {/foreach}
                    </select><br />
                    <label for="Mollie_Logger"><i>{$desc_logger}</i></label>
                </td>
            </tr>
            <tr>
                <td class="mollie_msg">
                    {$msg_save}
                </td>
                <td class="mollie_input">
                    <input type="submit" name="Mollie_Config_Save" value="{$val_save|escape}" />
                </td>
            </tr>
        </table>
    </fieldset>
</form>