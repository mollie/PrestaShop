<?php

namespace Mollie\Service;

use Cart;
use CartRule;
use Context;
use Db;
use Order;

class CartDuplicationService
{
    public function restoreCart($cartId)
    {
        $context = Context::getContext();
        $cart = new Cart($cartId);
        $duplication = $cart->duplicate();
        if ($duplication['success']) {
            /** @var Cart $duplicatedCart */
            $duplicatedCart = $duplication['cart'];
            foreach ($cart->getOrderedCartRulesIds() as $cartRuleId) {
                $duplicatedCart->addCartRule($cartRuleId['id_cart_rule']);
                $this->restoreCartRuleQuantity($cartId, $cartRuleId['id_cart_rule']);
            }
            $context->cookie->id_cart = $duplicatedCart->id;
            $context->cart = $duplicatedCart;
            CartRule::autoAddToCart($context);
            $context->cookie->write();
        }
    }

    private function restoreCartRuleQuantity($cartId, $cartRuleId)
    {
        $cartRule = new CartRule($cartRuleId);
        $cartRule->quantity++;
        $cartRule->update();

        $orderId = Order::getIdByCartId($cartId);
        $sql = 'DELETE FROM `ps_order_cart_rule` WHERE id_order = ' . (int) $orderId . ' AND id_cart_rule = ' . (int) $cartRuleId;
        DB::getInstance()->execute($sql);
    }
}