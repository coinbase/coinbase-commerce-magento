<?php
/**
 * Coinbase Commerce
 */

namespace CoinbaseCommerce\PaymentGateway\Controller\Webhook;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Filesystem\Io\File;
use CoinbaseCommerce\PaymentGateway\Api\CoinbaseRepositoryInterface;
use CoinbaseCommerce\PaymentGateway\Api\Data\CoinbaseInterfaceFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Registry;
use Magento\Sales\Model\Order\Status\HistoryFactory;
use Magento\Sales\Api\OrderStatusHistoryRepositoryInterface;
use Magento\Sales\Model\Service\InvoiceService;
use Magento\Framework\DB\Transaction;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

class Receiver extends Action
{
    /**
     * @var InvoiceSender
     */
    private $invoiceSender;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var JsonFactory
     */
    private $jsonResultFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var File
     */
    private $file;

    /**
     * @var CoinbaseRepositoryInterface
     */
    private $coinbaseRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var OrderStatusHistoryRepositoryInterface
     */
    private $historyRepository;

    /**
     * @var HistoryFactory
     */
    private $historyFactory;

    /**
     * @var \CoinbaseCommerce\PaymentGateway\Model\Coinbase
     */
    private $coinbaseFactory;

    /**
     * @var \Magento\Sales\Model\Service\InvoiceService
     */
    private $invoiceService;

    /**
     * @var \Magento\Framework\DB\Transaction
     */
    private $transaction;

    /**
     * @var InvoiceRepositoryInterface
     */
    private $invoiceRepository;

    /** @var  \Magento\Sales\Model\Order */
    private $order;
    private $coinStatus;

    public function __construct(
        Context $context,
        OrderRepositoryInterface $order,
        ScopeConfigInterface $scopeConfig,
        JsonFactory $jsonResultFactory,
        LoggerInterface $logger,
        File $file,
        CoinbaseRepositoryInterface $coinbaseRepository,
        CoinbaseInterfaceFactory $coinbaseInterfaceFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        Registry $registry,
        HistoryFactory $historyFactory,
        OrderStatusHistoryRepositoryInterface $historyRepository,
        InvoiceService $invoiceService,
        Transaction $transaction,
        InvoiceSender $invoiceSender,
        InvoiceRepositoryInterface $invoiceRepository
    ) {
        parent::__construct($context);
        $this->orderRepository = $order;
        $this->scopeConfig = $scopeConfig;
        $this->jsonResultFactory = $jsonResultFactory;
        $this->logger = $logger;
        $this->file = $file;
        $this->coinbaseRepository = $coinbaseRepository;
        $this->coinbaseFactory = $coinbaseInterfaceFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->registry = $registry;
        $this->historyFactory = $historyFactory;
        $this->historyRepository = $historyRepository;
        $this->invoiceService = $invoiceService;
        $this->transaction = $transaction;
        $this->invoiceSender = $invoiceSender;
        $this->invoiceRepository = $invoiceRepository;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Json|null
     * @throws LocalizedException
     */
    public function execute()
    {
        try {
            $input = $this->file->read('php://input');

            if (!$this->authenticate($input)) {
                return null;
            }

            $event = $this->getEventData(json_decode($input));
            if (!$this->getOrder($event)) {
                return null;
            }

            if ($event['type'] == 'charge:created' || $event['type'] == 'charge:pending') {
                $this->saveOrderDetails($event);
            } elseif ($event['coinbaseStatus'] == 'UNRESOLVED') {
                $this->orderHoldAction($event);
            } elseif ($event['type'] == 'charge:failed' && $event['coinbaseStatus'] == 'EXPIRED') {
                $this->paymentFailedAction();
            } elseif ($event['type'] == 'charge:confirmed' && $event['coinbaseStatus'] == 'COMPLETED') {
                $this->paymentSuccessAction($event);
            }

            $this->getResponse()->setStatusHeader(200);
            /** @var \Magento\Framework\Controller\Result\Json $result */
            $result = $this->jsonResultFactory->create();
            return $result;
        } catch (\Exception $e) {
            $this->logger->critical('Coinbase Webhook Receive Error', ['exception' => $e]);
            throw new LocalizedException(__('Something went wrong while Webhook recieving Api Response'));
        }
    }

    /**
     * @param $payload
     * @return bool
     */
    private function authenticate($payload)
    {
        $key = $this->scopeConfig->getValue('payment/coinbasemethod/api_secret');
        $headerSignature = $this->getRequest()->getHeader('X-CC-Webhook-Signature');
        $computedSignature = hash_hmac('sha256', $payload, $key);
        return $headerSignature === $computedSignature;
    }

    /**
     * Returns the generated comment or history object depending on arguments
     * @param string $comment
     * @param bool $addToHistory
     * @return \Magento\Framework\Phrase|\Magento\Sales\Api\Data\OrderStatusHistoryInterface|string
     * @throws LocalizedException
     */
    private function _createIpnComment($comment = '', $addToHistory = false)
    {
        try {
            $message = __('IPN "%1"', $this->coinStatus);
            if ($comment) {
                $message .= ' ' . $comment;
            }
            if ($addToHistory) {
                $message = $this->order->addStatusHistoryComment($message);
                $message->setIsCustomerNotified(null);
            }
            return $message;
        } catch (\Exception $e) {
            $this->logger->critical('Coinbase Webhook Receive IPN Error', ['exception' => $e]);
            throw new LocalizedException(__('Something went wrong while adding order status comment'));
        }
    }

    /**
     * @param $input
     * @return array
     */
    private function getEventData($input)
    {
        $data['incrementId'] = isset($input->event->data->metadata->store_increment_id) ?
            $input->event->data->metadata->store_increment_id : null;
        $data['chargeCode'] = $input->event->data->code;
        $data['type'] = $input->event->type;
        $data['timeline'] = end($input->event->data->timeline);
        $this->coinStatus = $data['coinbaseStatus'] = end($input->event->data->timeline)->status;
        $data['coinbasePayment'] = reset($input->event->data->payments);
        $data['eventDataNode'] = $input->event->data;
        return $data;
    }

    /**
     * @param $event
     * @throws LocalizedException
     */
    private function saveOrderDetails($event)
    {
        try {
            /** @var \CoinbaseCommerce\PaymentGateway\Model\Coinbase $coinbase */
            $coinbase = $this->coinbaseFactory->create();
            $coinbase->setCoinbaseChargeCode($event['chargeCode']);
            $coinbase->setStoreOrderId($event['incrementId']);
            $this->coinbaseRepository->save($coinbase);
            $this->logger->info("Coinbase Webhook Order Saved against Charge Code: " . $event['chargeCode']);
        } catch (\Exception $e) {
            $this->logger->critical('Coinbase Webhook Recieve Order Save Error', ['exception' => $e]);
            throw new LocalizedException(__('Something went wrong while saving Order'));
        }
    }

    /**
     * @param $event
     * @return \Magento\Sales\Api\Data\OrderInterface|mixed|null
     */
    private function getOrder($event)
    {
        if (empty($event['incrementId'])) {
            return null;
        }

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('increment_id', $event['incrementId'], 'eq')->create();
        $orderList = $this->orderRepository->getList($searchCriteria)->getItems();
        $this->order = $order = reset($orderList) ? reset($orderList) : null;
        return $order;
    }

    /**
     * Remove order from store
     */
    private function paymentFailedAction()
    {
        $history = $this->historyFactory->create();
        $history->setParentId($this->order->getId())->setComment('EXPIRED')
            ->setEntityName('order')
            ->setStatus(\Magento\Sales\Model\Order::STATE_CANCELED);
        $this->historyRepository->save($history);
        $this->order->cancel();
        return $this->orderRepository->save($this->order);
    }

    /**
     * Hold order state in store
     * @param $event
     */
    private function orderHoldAction($event)
    {
        /** @var \Magento\Sales\Api\Data\OrderStatusHistoryInterface $history */
        $history = $this->historyFactory->create();
        $history->setParentId($this->order->getId())
            ->setComment($event['timeline']->context)->setEntityName('order')
            ->setStatus(\Magento\Sales\Model\Order::STATE_HOLDED);

        $this->historyRepository->save($history);
        $this->order->hold();
        $this->orderRepository->save($this->order);
        $this->saveCoinsRecord($event);
    }

    /**
     * @param $event
     * @param $comment
     * @return mixed
     */
    private function updatePaymentOnSuccess($event, $comment)
    {
        $payment = $this->order->getPayment();
        $payment->setTransactionId($event['coinbasePayment']->transaction_id);
        $payment->setCurrencyCode($event['coinbasePayment']->value->local->currency);
        $payment->setPreparedMessage($this->_createIpnComment($comment));
        $payment->setShouldCloseParentTransaction(true);
        $payment->setIsTransactionClosed(0);
        $payment->registerCaptureNotification(
            $event['coinbasePayment']->value->local->amount,
            true
        );
        return $payment;
    }

    /**
     * @param $payment
     */
    private function updateInvoiceOnSuccess($payment)
    {
        $invoice = $payment->getCreatedInvoice();
        if ($invoice && !$this->order->getEmailSent()) {
            $this->order->send($this->order);
            $this->order->addStatusHistoryComment(
                __('You notified customer about invoice #%1.', $invoice->getIncrementId())
            )->setIsCustomerNotified(
                true
            );
            $this->orderRepository->save($this->order);
        }
        $this->sendInvoice();
    }

    /**
     * Generate invoice for order and send this to customer by email
     */
    private function sendInvoice()
    {
        if ($this->order->canInvoice()) {
            $invoice = $this->invoiceService->prepareInvoice($this->order);
            $invoice->register();
            $this->invoiceRepository->save($invoice);
            $transactionSave = $this->transaction->addObject(
                $invoice
            )->addObject(
                $invoice->getOrder()
            );
            $transactionSave->save();
            $this->invoiceSender->send($invoice);
            $this->order->addStatusHistoryComment(
                __('Notified customer about invoice #%1.', $invoice->getId())
            )
                ->setIsCustomerNotified(true)
                ->save();
        }
    }

    /**
     * @param $event
     * @return null
     */
    private function paymentSuccessAction($event)
    {
        if ($this->isPaymentRegistered()) {
            return null;
        }
        $crypto = $event['coinbasePayment']->value->crypto;
        $comment = $crypto->currency . ' ' . $crypto->amount;
        $payment = $this->updatePaymentOnSuccess($event, $comment);
        $this->orderRepository->save($this->order);
        $this->updateInvoiceOnSuccess($payment);
        $this->saveCoinsRecord($event);
    }

    /**
     * @param $event
     */
    private function saveCoinsRecord($event)
    {
        $network = $event['coinbasePayment']->network;
        $paymentRecord = $this->coinbaseRepository->getByChargeCode($event['chargeCode']);
        $paymentRecord->setCoinbaseStatus($event['coinbaseStatus']);
        $paymentRecord->setCoinsReceived($event['coinbasePayment']->value->crypto->amount);
        $paymentRecord->setReceivedCurrency($event['coinbasePayment']->value->crypto->currency);
        $paymentRecord->setTransactionId($event['coinbasePayment']->transaction_id);
        $paymentRecord->setCoinsExpected($event['eventDataNode']->pricing->$network->amount);
        $paymentRecord->setTotalPaid($event['coinbasePayment']->value->local->amount);
        $this->coinbaseRepository->save($paymentRecord);
    }

    /**
     * @return bool
     */
    private function isPaymentRegistered()
    {
        $comments = $this->order->getStatusHistories();
        $order = ["status" => $this->order->getStatus(), "notify" => $this->order->getCustomerNoteNotify()];
        $lastComment['status'] = reset($comments) ? reset($comments)->getStatus() : '';
        $lastComment['notify'] = reset($comments) ? reset($comments)->getIsCustomerNotified() : '';
        return $order === $lastComment;
    }
}
