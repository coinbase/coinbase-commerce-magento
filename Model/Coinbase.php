<?php

namespace CoinbaseCommerce\PaymentGateway\Model;

use CoinbaseCommerce\PaymentGateway\Api\Data\CoinbaseInterface;
use Magento\Framework\DataObject\IdentityInterface;

class Coinbase extends \Magento\Framework\Model\AbstractModel implements CoinbaseInterface, IdentityInterface
{
    /**
     * Coinbase Commerce Order
     */
    const CACHE_TAG = 'coinbase_order';

    /**
     * @var string
     */
    protected $_cacheTag = 'coinbase_order';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'coinbase_order';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('CoinbaseCommerce\PaymentGateway\Model\ResourceModel\Coinbase');
    }

    /**
     * Return unique ID(s) for each object in system
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId()
    {
        return $this->getData(self::ID);
    }

    /**
     * Get Store order increment id
     *
     * @return string
     */
    public function getStoreOrderId()
    {
        return $this->getData(self::STORE_ORDER_ID);
    }

    /**
     * Get Coinbase charge code
     *
     * @return string
     */
    public function getCoinbaseChargeCode()
    {
        return $this->getData(self::COINBASE_CHARGE_CODE);
    }

    /**
     * Gets the transaction id of coinbase payment.
     *
     * @return string|null Transaction Id.
     */
    public function getTransactionId()
    {
        return $this->getData(self::TRANSACTION_ID);
    }

    /**
     * Gets status of coinbase payment.
     *
     * @return string|null Status.
     */
    public function getCoinbaseStatus()
    {
        return $this->getData(self::COINBASE_STATUS);
    }

    /**
     * Gets the amount of coins received.
     *
     * @return float|null Amount received.
     */
    public function getCoinsReceived()
    {
        return $this->getData(self::COINS_RECEIVED);
    }

    /**
     * Gets the amount of coins expected from customer.
     *
     * @return float|null Expected Amount.
     */
    public function getCoinsExpected()
    {
        return $this->getData(self::COINS_EXPECTED);
    }

    /**
     * Gets currency in which coins are received.
     *
     * @return string|null Currency.
     */
    public function getReceivedCurrency()
    {
        return $this->getData(self::RECEIVED_CURRENCY);
    }

    /**
     * Gets the amount of money paid by cutomer.
     *
     * @return float|null Amount received.
     */
    public function getTotalPaid()
    {
        return $this->getData(self::TOTAL_PAID);
    }

    /**
     * Set ID
     *
     * @param int $id
     * @return \CoinbaseCommerce\PaymentGateway\Api\Data\CoinbaseInterface
     */
    public function setId($id)
    {
        return $this->setData(self::ID, $id);
    }

    /**
     * @param string $incrementId
     * @return \CoinbaseCommerce\PaymentGateway\Api\Data\CoinbaseInterface
     */
    public function setStoreOrderId($incrementId)
    {
        return $this->setData(self::STORE_ORDER_ID, $incrementId);
    }

    /**
     * @param string $charge
     * @return \CoinbaseCommerce\PaymentGateway\Api\Data\CoinbaseInterface
     */
    public function setCoinbaseChargeCode($charge)
    {
        return $this->setData(self::COINBASE_CHARGE_CODE, $charge);
    }

    /**
     * @param string $id
     * @return \CoinbaseCommerce\PaymentGateway\Api\Data\CoinbaseInterface
     */
    public function setTransactionId($id)
    {
        return $this->setData(self::TRANSACTION_ID, $id);
    }

    /**
     * @param string $status
     * @return \CoinbaseCommerce\PaymentGateway\Api\Data\CoinbaseInterface
     */
    public function setCoinbaseStatus($status)
    {
        return $this->setData(self::COINBASE_STATUS, $status);
    }

    /**
     * @param float $amount
     * @return \CoinbaseCommerce\PaymentGateway\Api\Data\CoinbaseInterface
     */
    public function setCoinsReceived($amount)
    {
        return $this->setData(self::COINS_RECEIVED, $amount);
    }

    /**
     * @param float $amount
     * @return \CoinbaseCommerce\PaymentGateway\Api\Data\CoinbaseInterface
     */
    public function setCoinsExpected($amount)
    {
        return $this->setData(self::COINS_EXPECTED, $amount);
    }

    /**
     * @param string $currency
     * @return \CoinbaseCommerce\PaymentGateway\Api\Data\CoinbaseInterface
     */
    public function setReceivedCurrency($currency)
    {
        return $this->setData(self::RECEIVED_CURRENCY, $currency);
    }

    /**
     * @param float $amount
     * @return \CoinbaseCommerce\PaymentGateway\Api\Data\CoinbaseInterface
     */
    public function setTotalPaid($amount)
    {
        return $this->setData(self::TOTAL_PAID, $amount);
    }
}
