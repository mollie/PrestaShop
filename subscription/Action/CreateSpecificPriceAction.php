<?php

namespace Mollie\Subscription\Action;

use Mollie\Subscription\DTO\CreateSpecificPriceData;
use Mollie\Subscription\Repository\SpecificPriceRepositoryInterface;

class CreateSpecificPriceAction
{
    /** @var SpecificPriceRepositoryInterface */
    private $specificPriceRepository;

    public function __construct(
        SpecificPriceRepositoryInterface $specificPriceRepository
    ) {
        $this->specificPriceRepository = $specificPriceRepository;
    }

    /**
     * @throws \Throwable
     */
    public function run(CreateSpecificPriceData $data): \SpecificPrice
    {
        /** @var \SpecificPrice[] $specificPrices */
        $specificPrices = $this->specificPriceRepository->findAllBy([
            'id_product' => $data->getProductId(),
            'id_product_attribute' => $data->getProductAttributeId(),
            'price' => $data->getPrice(),
            'id_customer' => $data->getCustomerId(),
            'id_shop' => $data->getShopId(),
            'id_currency' => $data->getCurrencyId(),
            'id_shop_group' => $data->getShopGroupId(),
            'id_country' => 0,
            'id_group' => 0,
            'from_quantity' => 0,
            'reduction' => 0,
            'reduction_type' => 'amount',
            'from' => '0000-00-00 00:00:00',
            'to' => '0000-00-00 00:00:00',
        ]);

        foreach ($specificPrices as $specificPrice) {
            $specificPrice->delete();
        }

        $specificPrice = new \SpecificPrice();

        $specificPrice->id_product = $data->getProductId();
        $specificPrice->id_product_attribute = $data->getProductAttributeId();
        $specificPrice->price = $data->getPrice();
        $specificPrice->id_customer = $data->getCustomerId();
        $specificPrice->id_shop = $data->getShopId();
        $specificPrice->id_currency = $data->getCurrencyId();
        $specificPrice->id_shop_group = $data->getShopGroupId();
        $specificPrice->id_country = 0;
        $specificPrice->id_group = 0;
        $specificPrice->from_quantity = 0;
        $specificPrice->reduction = 0;
        $specificPrice->reduction_type = 'amount';
        $specificPrice->from = '0000-00-00 00:00:00';
        $specificPrice->to = '0000-00-00 00:00:00';

        $specificPrice->add();

        return $specificPrice;
    }
}
