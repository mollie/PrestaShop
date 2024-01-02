<?php

namespace Mollie\Tests\Integration\Install;

use Mollie\Config\Config;
use Mollie\Tests\Integration\BaseTestCase;

class OrderStateInstallerTest extends BaseTestCase
{
    /** @dataProvider requiredOrderStatusesDataProvider */
    public function testItHasRequiredModuleOrderStatuses(
        string $key,
        string $color,
        bool $sendEmail,
        bool $logable,
        bool $delivery,
        bool $invoice,
        bool $shipped,
        bool $paid,
        bool $pdfInvoice
    ) {
        $this->assertDatabaseHas(\Configuration::class, [
            'name' => $key,
        ]);

        //NOTE cannot just check by "name"  Field name is declared as lang field but is used in non multilang context
        $this->assertDatabaseHas(\OrderState::class, [
            'id_order_state' => (int) \Configuration::get($key),
            'color' => $color,
            'send_email' => $sendEmail,
            'logable' => $logable,
            'delivery' => $delivery,
            'invoice' => $invoice,
            'shipped' => $shipped,
            'paid' => $paid,
            'pdf_invoice' => $pdfInvoice,
            'module_name' => 'mollie',
        ]);
    }

    public function requiredOrderStatusesDataProvider()
    {
        return [
            [
                'key' => Config::MOLLIE_STATUS_PARTIAL_REFUND,
                'color' => '#6F8C9F',
                'sendEmail' => false,
                'logable' => false,
                'delivery' => false,
                'invoice' => false,
                'shipped' => false,
                'paid' => false,
                'pdfInvoice' => false,
            ],
            [
                'key' => Config::MOLLIE_STATUS_AWAITING,
                'color' => '#4169E1',
                'sendEmail' => false,
                'logable' => false,
                'delivery' => false,
                'invoice' => false,
                'shipped' => false,
                'paid' => false,
                'pdfInvoice' => false,
            ],
            [
                'key' => Config::MOLLIE_STATUS_PARTIALLY_SHIPPED,
                'color' => '#8A2BE2',
                'sendEmail' => false,
                'logable' => false,
                'delivery' => false,
                'invoice' => false,
                'shipped' => false,
                'paid' => false,
                'pdfInvoice' => false,
            ],
            [
                'key' => Config::MOLLIE_STATUS_ORDER_COMPLETED,
                'color' => '#3d7d1c',
                'sendEmail' => true,
                'logable' => false,
                'delivery' => false,
                'invoice' => false,
                'shipped' => false,
                'paid' => false,
                'pdfInvoice' => false,
            ],
            [
                'key' => Config::MOLLIE_AUTHORIZABLE_PAYMENT_STATUS_AUTHORIZED,
                'color' => '#8A2BE2',
                'sendEmail' => true,
                'logable' => true,
                'delivery' => false,
                'invoice' => true,
                'shipped' => false,
                'paid' => true,
                'pdfInvoice' => true,
            ],
            [
                'key' => Config::MOLLIE_AUTHORIZABLE_PAYMENT_STATUS_SHIPPED,
                'color' => '#8A2BE2',
                'sendEmail' => true,
                'logable' => true,
                'delivery' => true,
                'invoice' => false,
                'shipped' => true,
                'paid' => true,
                'pdfInvoice' => true,
            ],
            [
                'key' => Config::MOLLIE_STATUS_CHARGEBACK,
                'color' => '#E74C3C',
                'sendEmail' => false,
                'logable' => false,
                'delivery' => false,
                'invoice' => false,
                'shipped' => false,
                'paid' => false,
                'pdfInvoice' => false,
            ],
        ];
    }
}
