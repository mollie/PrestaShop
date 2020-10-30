<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * @param Mollie $module
 * @return bool
 */
function upgrade_module_4_1_0($module)
{
    $sql = '
        ALTER TABLE ' . _DB_PREFIX_ . 'mol_payment_method
        ADD `position` INT(10);
    ';

    $isAdded = Db::getInstance()->execute($sql);

    if (!$isAdded) {
        return false;
    }

    /** @var \Mollie\Repository\PaymentMethodRepositoryInterface $paymentMethodsRepo */
    $paymentMethodsRepo = $module->getContainer(\Mollie\Repository\PaymentMethodRepositoryInterface::class);
    $paymentMethods = $paymentMethodsRepo->findAll();

    $isUpdated = true;
    // adding positions for all payments in order they exist in database
    $iteration = 0;
    /** @var MolPaymentMethod $paymentMethod */
    foreach ($paymentMethods as $paymentMethod) {
        $paymentMethod->position = $iteration;

        $iteration++;

        $isUpdated &= $paymentMethod->update();
    }

    return $isUpdated;
}
