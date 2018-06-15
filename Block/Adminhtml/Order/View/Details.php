<?php
/**
 * Coinbase Commerce
 */

namespace CoinbaseCommerce\PaymentGateway\Block\Adminhtml\Order\View;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Api\OrderRepositoryInterface;
use CoinbaseCommerce\PaymentGateway\Api\CoinbaseRepositoryInterface;

class Details extends Template
{
    private $orderRepository;
    private $coinbaseRepository;

    public function __construct(
        Context $context,
        OrderRepositoryInterface $orderRepository,
        CoinbaseRepositoryInterface $coinbaseRepository
    ) {
        parent::__construct($context);
        $this->orderRepository = $orderRepository;
        $this->coinbaseRepository = $coinbaseRepository;
    }

    private function getOrder()
    {
        $order = $this->orderRepository->get($this->getRequest()->getParam('order_id'));
        return $order;
    }

    private function getCoinbaseRecord()
    {
        return $this->coinbaseRepository->getByIncrementId($this->getOrder()->getIncrementId());
    }

    public function isPaymentMadeInCoinbase()
    {
        return $this->getOrder()->getPayment()->getMethod() == 'coinbasemethod';
    }

    public function getCoinsDetail()
    {
        $record = $this->getCoinbaseRecord();
        $data['expectedCoins'] = $record->getCoinsExpected() . ' ' . $record->getReceivedCurrency();
        $data['amount'] = $record->getCoinsReceived() . ' ' . $record->getReceivedCurrency();
        $data['status'] = $record->getCoinbaseStatus();
        $data['code'] = $record->getCoinbaseChargeCode();
        $data['transactionId'] = $record->getTransactionId();
        $data['totalPaid'] = $record->getTotalPaid();
        $data['orderPlacedCurrency'] = $this->getOrder()->getOrderCurrencyCode();
        return $data;
    }
}
