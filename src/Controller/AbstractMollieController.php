<?php

namespace Mollie\Controller;

use Mollie\Config\Config;

class AbstractMollieController extends \ModuleFrontControllerCore
{
    public function redirectWithNotifications()
    {
        $notifications = json_encode(array(
            'error' => $this->errors,
            'warning' => $this->warning,
            'success' => $this->success,
            'info' => $this->info,
        ));

        if (session_status() == PHP_SESSION_ACTIVE) {
            $_SESSION['notifications'] = $notifications;
        } elseif (session_status() == PHP_SESSION_NONE) {
            session_start();
            $_SESSION['notifications'] = $notifications;
        } else {
            setcookie('notifications', $notifications);
        }

        if (!Config::isVersion17()) {
            $this->context->cookie->mollie_payment_canceled_error =
                json_encode($this->warning);
        }
        return call_user_func_array(['Tools', 'redirect'], func_get_args());
    }

}