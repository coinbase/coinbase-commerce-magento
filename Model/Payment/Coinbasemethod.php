<?php
/**
 * A Magento 2 module named CoinbaseCommerce/PaymentGateway
 * Copyright (C) 2017 Coinbase
 *
 * This file included in CoinbaseCommerce/PaymentGateway is licensed under OSL 3.0
 *
 * http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * Please see LICENSE.txt for the full text of the OSL 3.0 license
 */

namespace CoinbaseCommerce\PaymentGateway\Model\Payment;

class Coinbasemethod extends \Magento\Payment\Model\Method\AbstractMethod
{
    protected $_code = "coinbasemethod";
    protected $_isOffline = true;

    public function isAvailable(
        \Magento\Quote\Api\Data\CartInterface $quote = null
    ) {
        $apiKey = $this->_scopeConfig->getValue(
            'payment/coinbasemethod/api_key',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $apiSecret = $this->_scopeConfig->getValue(
            'payment/coinbasemethod/api_secret',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if (!$apiKey || !$apiSecret) {
            return false;
        }
        return parent::isAvailable($quote);
    }
}
