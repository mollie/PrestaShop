<div class="mollie_refund">
    <div class="mollie_panel">
        <div class="mollie_refund_message">
            <img src="{$img_src}" alt="" />{$msg_title}
        </div>
        <div class="mollie_refund_button_box">
            {if $status === 'form'}
                <form action="{$smarty.server.REQUEST_URI|escape}" method="post">
                    <div class="mollie_refund_desc">{$msg_description}</div>
                    <input name="Mollie_Refund" type="submit" class="mollie_refund_button" value="{$msg_button}" onclick="return confirm('Are you sure you want to refund this order? This cannot be undone.')" />
                </form>
            {elseif $status === 'fail'}
                <div class="mollie_refund_fail">{$msg_fail}</div>
                <div class="mollie_refund_details">{$msg_details}</div>
            {elseif $status === 'success'}
                <div class="mollie_refund_success">{$msg_success}</div><br />
                <div class="mollie_refund_details">{$msg_details}</div>
            {/if}
        </div>
    </div>
</div>