<?php

namespace Baniwal\Blog\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * Class InstallData
 * @package Baniwal\Blog\Setup
 */
class InstallData implements InstallDataInterface
{
    /**
     * {@inheritdoc}
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        /** Add root category */
        $sampleTemplates = [
            'path' => '1',
            'position' => 0,
            'children_count' => 0,
            'level' => 0,
            'name' => 'ROOT',
            'url_key' => 'root'
        ];
        $setup->getConnection()->insert($setup->getTable('baniwal_blog_category'), $sampleTemplates);

        $installer->endSetup();
    }
}
