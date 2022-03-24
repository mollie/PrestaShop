<?php

namespace Mollie\Application\CommandHandler;

use Cart;
use Mollie\Application\Command\UpdateApplePayShippingMethod;

final class UpdateApplePayShippingMethodHandler
{
   public function handle(UpdateApplePayShippingMethod $command): array
   {
       $cart = new Cart($command->getCartId());
       $cart->id_carrier = $command->getCarrierId();
       $cart->setDeliveryOption([
           $cart->id_address_delivery => $command->getCarrierId() . ','
       ]);
       $cart->update();

       return [
           'success' => true,
           'data' => [
               'amount' => $cart->getOrderTotal(true, Cart::BOTH, null, $command->getCarrierId())
           ]
       ];
   }
}
