<?php
/**
 * Coinbase Commerce
 */

namespace CoinbaseCommerce\PaymentGateway\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Store\Model\StoreManagerInterface;

class InstallData implements InstallDataInterface
{
    private $storeManagerInterface;

    public function __construct(StoreManagerInterface $storeManager)
    {
        $this->storeManagerInterface = $storeManager;
    }

    /**
     * {@inheritdoc}
     */
    public function install(
        ModuleDataSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $base_url = $this->storeManagerInterface->getStore()->getBaseUrl(
            \Magento\Framework\UrlInterface::URL_TYPE_WEB,
            true
        );
        $base_url = str_replace("http://", "https://", $base_url);

        $data_callback_url = [
            'scope' => 'default',
            'scope_id' => 0,
            'path' => 'payment/coinbasemethod/webhook_url',
            'value' => $base_url . 'coinbasecommerce/webhook/receiver',
        ];
        $setup->getConnection()
            ->insertOnDuplicate($setup->getTable('core_config_data'), $data_callback_url, ['value']);
    }
}
