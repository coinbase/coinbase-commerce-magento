<?php
/**
 * Coinbase Commerce
 */

namespace CoinbaseCommerce\PaymentGateway\Controller\Webhook;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Checkout\Model\Session\Proxy;
use Magento\Framework\App\Request\Http;
use Magento\Sales\Model\Order;

class Cancel extends Action
{
    /**
     * @var Proxy
     */
    private $checkoutSession;

    /**
     * @var Http
     */
    private $request;

    public function __construct(
        Context $context,
        Proxy $checkoutSession,
        Http $request
    ) {
        parent::__construct($context);
        $this->checkoutSession = $checkoutSession;
        $this->request = $request;
    }

    public function execute()
    {
        $order = $this->checkoutSession->getLastRealOrder();
        $orderId = $order->getId();

        if ($orderId && $orderId == (int)$this->request->get('order_id')) {
            $this->cancelCurrentOrder();
            $this->checkoutSession->restoreQuote();
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('checkout/cart');
        return $resultRedirect;
    }


    public function cancelCurrentOrder()
    {
        $order = $this->checkoutSession->getLastRealOrder();

        if ($order->getId() && !$order->isCanceled()) {
            $order->cancel();
            $order->addStatusToHistory(Order::STATE_CANCELED, __('Canceled by customer.'));
            $order->save();
        }
    }
}
