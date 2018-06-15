<?php
/**
 * Coinbase Commerce
 */

namespace CoinbaseCommerce\PaymentGateway\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function install(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $installer = $setup;
        $installer->startSetup();

        $tableName = $installer->getTable('coinbase_commerce_orders');

        // Check if the table already exists
        if ($installer->getConnection()->isTableExists($tableName) != true) {
            $table = $installer->getConnection()
                ->newTable($tableName)
                ->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true
                    ],
                    'ID'
                )
                ->addColumn(
                    'store_order_id',
                    Table::TYPE_TEXT,
                    '32',
                    [ 'unsigned' => true, 'nullable' => false ],
                    'Order ID'
                )
                ->addColumn(
                    'coinbase_charge_code',
                    Table::TYPE_TEXT,
                    '10',
                    [ 'unsigned' => true, 'nullable' => false ],
                    'Coinbase Charge Code'
                )
                ->addColumn(
                    'transaction_id',
                    Table::TYPE_TEXT,
                    '225',
                    [ 'unsigned' => true, 'nullable' => true ],
                    'Transaction Id'
                )
                ->addColumn(
                    'coinbase_status',
                    Table::TYPE_TEXT,
                    '25',
                    [ 'unsigned' => true, 'nullable' => true ],
                    'Status of coinbase payment'
                )
                ->addColumn(
                    'coins_received',
                    Table::TYPE_FLOAT,
                    '25',
                    [ 'unsigned' => true, 'nullable' => true ],
                    'Coins received'
                )
                ->addColumn(
                    'coins_expected',
                    Table::TYPE_FLOAT,
                    '25',
                    [ 'unsigned' => true, 'nullable' => true ],
                    'Coins expected'
                )
                ->addColumn(
                    'received_currency',
                    Table::TYPE_TEXT,
                    '6',
                    [ 'unsigned' => true, 'nullable' => true ],
                    'Currency of Coins received'
                )
                ->addColumn(
                    'total_paid',
                    Table::TYPE_FLOAT,
                    '25',
                    [ 'unsigned' => true, 'nullable' => true ],
                    'Amount paid in money'
                )
                ->addIndex(
                    $installer->getIdxName(
                        $tableName,
                        ['store_order_id'],
                        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                    ),
                    ['store_order_id'],
                    ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
                )
                ->addForeignKey(
                    $installer->getFkName($tableName, 'store_order_id', 'sales_order', 'increment_id'),
                    'store_order_id',
                    $installer->getTable('sales_order'),
                    'increment_id',
                    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
                )
                ->setComment('Coinbase Commerce orders');

            $installer->getConnection()->createTable($table);
        }

        $setup->endSetup();
    }
}
