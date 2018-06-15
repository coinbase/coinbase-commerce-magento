<?php

namespace CoinbaseCommerce\PaymentGateway\Api\Data;

interface CoinbaseInterface
{
    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const ID       = 'id';
    const STORE_ORDER_ID = 'store_order_id';
    const COINBASE_CHARGE_CODE = 'coinbase_charge_code';
    const TRANSACTION_ID = 'transaction_id';
    const COINBASE_STATUS = 'coinbase_status';
    const COINS_RECEIVED = 'coins_received';
    const COINS_EXPECTED = 'coins_expected';
    const RECEIVED_CURRENCY = 'received_currency';
    const TOTAL_PAID = 'total_paid';
    /**
     * @return int
     */
    public function getId();

    /**
     * @param int $id
     * @return void
     */
    public function setId($id);

    /**
     * Gets the magento store increment ID for the order.
     *
     * @return string|null Increment ID.
     */
    public function getStoreOrderId();

    /**
     * @param string $incrementId
     * @return void
     */
    public function setStoreOrderId($incrementId);

    /**
     * Gets charge code provided by coinbase.
     *
     * @return string|null Increment ID.
     */
    public function getCoinbaseChargeCode();

    /**
     * @param string $code
     * @return void
     */
    public function setCoinbaseChargeCode($code);

    /**
     * Gets the transaction id of coinbase payment.
     *
     * @return string|null Transaction Id.
     */
    public function getTransactionId();

    /**
     * @param string $id
     * @return void
     */
    public function setTransactionId($id);

    /**
     * Gets status of coinbase payment.
     *
     * @return string|null Status.
     */
    public function getCoinbaseStatus();

    /**
     * @param string $status
     * @return void
     */
    public function setCoinbaseStatus($status);

    /**
     * Gets the amount of coins received.
     *
     * @return float|null Amount received.
     */
    public function getCoinsReceived();

    /**
     * @param float $amount
     * @return void
     */
    public function setCoinsReceived($amount);

    /**
     * Gets the amount of coins expected from customer.
     *
     * @return float|null Amount received.
     */
    public function getCoinsExpected();

    /**
     * @param float $amount
     * @return void
     */
    public function setCoinsExpected($amount);

    /**
     * Gets currency in which coins are received.
     *
     * @return string|null Currency.
     */
    public function getReceivedCurrency();

    /**
     * @param string $currency
     * @return void
     */
    public function setReceivedCurrency($currency);

    /**
     * Gets the amount of money paid, converted from coins.
     *
     * @return float|null Amount received.
     */
    public function getTotalPaid();

    /**
     * @param float $amount
     * @return void
     */
    public function setTotalPaid($amount);
}
