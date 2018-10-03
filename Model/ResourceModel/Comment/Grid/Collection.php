<?php

namespace Baniwal\Blog\Model\ResourceModel\Comment\Grid;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;
use Psr\Log\LoggerInterface as Logger;

class Collection extends SearchResult
{
    public function __construct(
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
        $mainTable = 'baniwal_blog_comment',
        $resourceModel = '\Baniwal\Blog\Model\ResourceModel\Comment'
    )
    {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel);
    }

    protected function _initSelect()
    {
        parent::_initSelect();

        $this->addPostName();
        $this->addCustomerName();
        return $this;
    }

    public function addFieldToFilter($field, $condition = null)
    {
        if ($field == 'customer_name') {
            return parent::addFieldToFilter(['firstname', 'lastname'], [$condition, $condition]);
        } else if ($field == 'post_name') {
            $field = 'mp.name';
        } else if ($field == 'created_at') {
            $field = 'main_table.created_at';
        }

        return parent::addFieldToFilter($field, $condition);
    }

    public function addPostName()
    {
        $this->getSelect()->joinLeft(
            ['mp' => $this->getTable('baniwal_blog_post')],
            "main_table.post_id = mp.post_id",
            ['post_name' => 'name']
        );
        return $this;
    }

    public function addCustomerName()
    {
        $this->getSelect()->joinLeft(
            ['ce' => $this->getTable('customer_entity')],
            "main_table.entity_id = ce.entity_id",
            ['firstname', 'lastname']
        )->columns([
            "customer_name" => new \Zend_Db_Expr("CONCAT(`ce`.`firstname`,' ',`ce`.`lastname`)")
        ]);

        return $this;
    }
}
