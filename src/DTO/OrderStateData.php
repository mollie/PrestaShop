<?php
/**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 *
 * @see        https://github.com/mollie/PrestaShop
 * @codingStandardsIgnoreStart
 */

namespace Mollie\DTO;

if (!defined('_PS_VERSION_')) {
    exit;
}

class OrderStateData
{
    /** @var string */
    private $name;
    /** @var bool */
    private $sendEmail;
    /** @var string */
    private $color;
    /** @var bool */
    private $logable;
    /** @var bool */
    private $delivery;
    /** @var bool */
    private $invoice;
    /** @var bool */
    private $shipped;
    /** @var bool */
    private $paid;
    /** @var string */
    private $template;
    /** @var bool */
    private $pdfInvoice;

    public function __construct(
        string $name,
        string $color,
        bool $sendEmail = false,
        bool $logable = false,
        bool $delivery = false,
        bool $invoice = false,
        bool $shipped = false,
        bool $paid = false,
        string $template = '',
        bool $pdfInvoice = false
    ) {
        $this->name = $name;
        $this->sendEmail = $sendEmail;
        $this->color = $color;
        $this->logable = $logable;
        $this->delivery = $delivery;
        $this->invoice = $invoice;
        $this->shipped = $shipped;
        $this->paid = $paid;
        $this->template = $template;
        $this->pdfInvoice = $pdfInvoice;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function isSendEmail(): bool
    {
        return $this->sendEmail;
    }

    public function getColor(): string
    {
        return $this->color;
    }

    public function isLogable(): bool
    {
        return $this->logable;
    }

    public function isDelivery(): bool
    {
        return $this->delivery;
    }

    public function isInvoice(): bool
    {
        return $this->invoice;
    }

    public function isShipped(): bool
    {
        return $this->shipped;
    }

    public function isPaid(): bool
    {
        return $this->paid;
    }

    public function getTemplate(): string
    {
        return $this->template;
    }

    public function isPdfInvoice(): bool
    {
        return $this->pdfInvoice;
    }
}
