<?php

namespace CoinbaseCommerce\PaymentGateway\Model\ResourceModel\Coinbase;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'id';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('CoinbaseCommerce\PaymentGateway\Model\Coinbase', 'CoinbaseCommerce\PaymentGateway\Model\ResourceModel\Coinbase');
    }
}
