<?php
/**
 * Coinbase Commerce
 */

namespace CoinbaseCommerce\PaymentGateway\Controller\Webhook;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Model\OrderFactory;
use Magento\Checkout\Model\Session\Proxy;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;

class Redirect extends Action
{
    /**
     * @var Proxy
     */
    private $checkoutSession;

    /**
     * @var OrderFactory
     */
    private $orderFactory;

    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Proxy $checkoutSession,
        OrderFactory $orderFactory,
        CartRepositoryInterface $cartRepository
    ) {
        parent::__construct($context);
        $this->checkoutSession = $checkoutSession;
        $this->orderFactory = $orderFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->quoteRepository = $cartRepository;
    }

    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = null;
        try {
            if ($this->checkoutSession->getLastRealOrderId()) {
                $order = $this->orderFactory->create()->loadByIncrementId($this->checkoutSession->getLastRealOrderId());
                if ($order->getStatus() == 'processing') {
                    $this->_redirect('checkout/onepage/success', ['_secure' => true]);
                } else {
                    $this->checkoutSession->restoreQuote();
                }
            }
            $resultPage = $this->resultPageFactory->create();
            return $resultPage;
        } catch (\Exception $e) {
            $this->logger->critical('Coinbase Webhook Redirect Error', ['exception' => $e]);
            throw new LocalizedException(__('Something went wrong while redirecting'));
        }
    }
}
