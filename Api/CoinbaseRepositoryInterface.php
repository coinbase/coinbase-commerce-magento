<?php
/**
 * Coinbase Commerce
 */
namespace CoinbaseCommerce\PaymentGateway\Api;

/**
 * Coinbase Commerce repository interface.
 */
interface CoinbaseRepositoryInterface
{
    /**
     * Lists orders that match specified search criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria The search criteria.
     * @return \CoinbaseCommerce\PaymentGateway\Api\Data\CoinbaseSearchResultInterface Order search result interface.
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Loads a specified order.
     *
     * @param int $id The order ID.
     * @return \CoinbaseCommerce\PaymentGateway\Api\Data\CoinbaseInterface Order interface.
     */
    public function get($id);

    /**
     * Loads a specified order.
     *
     * @param string $incrementId Increment id of order in store.
     * @return \CoinbaseCommerce\PaymentGateway\Api\Data\CoinbaseInterface Order interface.
     */
    public function getByIncrementId($incrementId);

    /**
     * Loads a specified order.
     *
     * @param string $chargeCode Charge code of coinbase order.
     * @return \CoinbaseCommerce\PaymentGateway\Api\Data\CoinbaseInterface Order interface.
     */
    public function getByChargeCode($chargeCode);

    /**
     * Deletes a specified order.
     *
     * @param \CoinbaseCommerce\PaymentGateway\Api\Data\CoinbaseInterface $entity The order ID.
     * @return bool
     */
    public function delete(\CoinbaseCommerce\PaymentGateway\Api\Data\CoinbaseInterface $entity);

    /**
     * Performs persist operations for a specified order.
     *
     * @param \CoinbaseCommerce\PaymentGateway\Api\Data\CoinbaseInterface $entity The order ID.
     * @return \CoinbaseCommerce\PaymentGateway\Api\Data\CoinbaseInterface Order interface.
     */
    public function save(\CoinbaseCommerce\PaymentGateway\Api\Data\CoinbaseInterface $entity);
}
