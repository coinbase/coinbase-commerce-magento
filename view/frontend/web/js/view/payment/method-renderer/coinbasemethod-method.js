define(
    [
        'jquery',
        'Magento_Checkout/js/view/payment/default',
        'mage/url',
        'Magento_Customer/js/customer-data',
        'Magento_Checkout/js/model/error-processor',
        'Magento_Checkout/js/model/full-screen-loader'
    ],
    function ($, Component, url, customerData, errorProcessor, fullScreenLoader) {
        'use strict';
        return Component.extend({
            redirectAfterPlaceOrder: false,
            defaults: {
                template: 'CoinbaseCommerce_PaymentGateway/payment/coinbasemethod'
            },
            getMailingAddress: function () {
                return window.checkoutConfig.payment.checkmo.mailingAddress;
            },
            afterPlaceOrder: function () {
                var custom_controller_url = url.build('coinbasecommerce/start/index'); //your custom controller url
                $.post(custom_controller_url, 'json')
                    .done(function (response) {
                        // customerData.invalidate(['cart']);
                        window.location.href = response.redirectUrl;
                    })
                    .fail(function (response) {
                        errorProcessor.process(response, this.messageContainer);
                    })
                    .always(function () {
                        fullScreenLoader.stopLoader();
                    });
            },
			/** Returns payment currency images link path */
			getPaymentImage: function () {
				var custom_image = [
				require.toUrl('CoinbaseCommerce_PaymentGateway/images/ethereum.svg'),
				require.toUrl('CoinbaseCommerce_PaymentGateway/images/bitcoin.svg'),
				require.toUrl('CoinbaseCommerce_PaymentGateway/images/litecoin.svg'),
				require.toUrl('CoinbaseCommerce_PaymentGateway/images/bitcoin_cash.svg')
				];
				return custom_image;
			}			
        });
    }
);
