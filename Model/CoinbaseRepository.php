<?php

namespace CoinbaseCommerce\PaymentGateway\Model;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\NoSuchEntityException;
use CoinbaseCommerce\PaymentGateway\Api\Data\CoinbaseInterface;
use CoinbaseCommerce\PaymentGateway\Api\Data\CoinbaseSearchResultInterface;
use CoinbaseCommerce\PaymentGateway\Api\Data\CoinbaseSearchResultInterfaceFactory;
use CoinbaseCommerce\PaymentGateway\Api\CoinbaseRepositoryInterface;
use CoinbaseCommerce\PaymentGateway\Model\ResourceModel\Coinbase\Collection as CoinbaseCollectionFactory;
use CoinbaseCommerce\PaymentGateway\Model\ResourceModel\Coinbase\Collection;

class CoinbaseRepository implements CoinbaseRepositoryInterface
{
    /**
     * @var Coinbase
     */
    private $coinbaseFactory;

    /**
     * @var CoinbaseCollectionFactory
     */
    private $coinbaseCollectionFactory;

    /**
     * @var CoinbaseSearchResultInterfaceFactory
     */
    private $searchResultFactory;

    public function __construct(
        CoinbaseFactory $coinbaseFactory,
        CoinbaseCollectionFactory $coinbaseCollectionFactory,
        CoinbaseSearchResultInterfaceFactory $coinbaseSearchResultInterfaceFactory
    ) {
        $this->coinbaseFactory = $coinbaseFactory;
        $this->coinbaseCollectionFactory = $coinbaseCollectionFactory;
        $this->searchResultFactory = $coinbaseSearchResultInterfaceFactory;
    }

    public function get($id)
    {
        $coinbase = $this->coinbaseFactory->create();
        $coinbase->getResource()->load($coinbase, $id);
        if (!$coinbase->getId()) {
            throw new NoSuchEntityException(__('Unable to find coinbase order with ID "%1"', $id));
        }
        return $coinbase;
    }

    public function getByIncrementId($incrementId)
    {
        $coinbase = $this->coinbaseFactory->create();
        $coinbase->getResource()->load($coinbase, $incrementId, 'store_order_id');
        if (!$coinbase->getId()) {
            throw new NoSuchEntityException(__('Unable to find coinbase order with store id "%1"', $incrementId));
        }
        return $coinbase;
    }

    public function getByChargeCode($chargeCode)
    {
        $coinbase = $this->coinbaseFactory->create();
        $coinbase->getResource()->load($coinbase, $chargeCode, 'coinbase_charge_code');
        if (!$coinbase->getId()) {
            throw new NoSuchEntityException(__('Unable to find coinbase order with charge code "%1"', $chargeCode));
        }
        return $coinbase;
    }

    public function save(CoinbaseInterface $coinbase)
    {
        $coinbase->getResource()->save($coinbase);
        return $coinbase;
    }

    public function delete(CoinbaseInterface $coinbase)
    {
        $coinbase->getResource()->delete($coinbase);
    }

    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $collection = $this->collectionFactory->create();

        $this->addFiltersToCollection($searchCriteria, $collection);
        $this->addSortOrdersToCollection($searchCriteria, $collection);
        $this->addPagingToCollection($searchCriteria, $collection);

        $collection->load();

        return $this->buildSearchResult($searchCriteria, $collection);
    }

    private function addFiltersToCollection(SearchCriteriaInterface $searchCriteria, Collection $collection)
    {
        foreach ($searchCriteria->getFilterGroups() as $filterGroup) {
            $fields = $conditions = [];
            foreach ($filterGroup->getFilters() as $filter) {
                $fields[] = $filter->getField();
                $conditions[] = [$filter->getConditionType() => $filter->getValue()];
            }
            $collection->addFieldToFilter($fields, $conditions);
        }
    }

    private function addSortOrdersToCollection(SearchCriteriaInterface $searchCriteria, Collection $collection)
    {
        foreach ((array)$searchCriteria->getSortOrders() as $sortOrder) {
            $direction = $sortOrder->getDirection() == SortOrder::SORT_ASC ? 'asc' : 'desc';
            $collection->addOrder($sortOrder->getField(), $direction);
        }
    }

    private function addPagingToCollection(SearchCriteriaInterface $searchCriteria, Collection $collection)
    {
        $collection->setPageSize($searchCriteria->getPageSize());
        $collection->setCurPage($searchCriteria->getCurrentPage());
    }

    private function buildSearchResult(SearchCriteriaInterface $searchCriteria, Collection $collection)
    {
        $searchResults = $this->searchResultFactory->create();

        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());

        return $searchResults;
    }
}
