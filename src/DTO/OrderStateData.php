<?php

namespace Mollie\DTO;

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

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return bool
     */
    public function isSendEmail(): bool
    {
        return $this->sendEmail;
    }

    /**
     * @return string
     */
    public function getColor(): string
    {
        return $this->color;
    }

    /**
     * @return bool
     */
    public function isLogable(): bool
    {
        return $this->logable;
    }

    /**
     * @return bool
     */
    public function isDelivery(): bool
    {
        return $this->delivery;
    }

    /**
     * @return bool
     */
    public function isInvoice(): bool
    {
        return $this->invoice;
    }

    /**
     * @return bool
     */
    public function isShipped(): bool
    {
        return $this->shipped;
    }

    /**
     * @return bool
     */
    public function isPaid(): bool
    {
        return $this->paid;
    }

    /**
     * @return string
     */
    public function getTemplate(): string
    {
        return $this->template;
    }

    /**
     * @return bool
     */
    public function isPdfInvoice(): bool
    {
        return $this->pdfInvoice;
    }
}
