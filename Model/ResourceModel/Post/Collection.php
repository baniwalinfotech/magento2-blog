<?php

namespace Baniwal\Blog\Model\ResourceModel\Post;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'post_id';

    protected $_eventPrefix = 'baniwal_blog_post_collection';

    protected $_eventObject = 'post_collection';

    protected function _construct()
    {
        $this->_init('Baniwal\Blog\Model\Post', 'Baniwal\Blog\Model\ResourceModel\Post');
    }

    public function getSelectCountSql()
    {
        $countSelect = parent::getSelectCountSql();
        $countSelect->reset(\Zend_Db_Select::GROUP);

        return $countSelect;
    }

    protected function _toOptionArray($valueField = 'post_id', $labelField = 'name', $additional = [])
    {
        return parent::_toOptionArray($valueField, $labelField, $additional);
    }
}
