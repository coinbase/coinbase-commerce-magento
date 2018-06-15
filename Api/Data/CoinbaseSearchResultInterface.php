<?php

namespace CoinbaseCommerce\PaymentGateway\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

interface CoinbaseSearchResultInterface extends SearchResultsInterface
{
    /**
     * @return \CoinbaseCommerce\PaymentGateway\Api\Data\CoinbaseInterface[]
     */
    public function getItems();

    /**
     * @param \CoinbaseCommerce\PaymentGateway\Api\Data\CoinbaseInterface[] $items
     * @return void
     */
    public function setItems(array $items);
}
