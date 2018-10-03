<?php

namespace Baniwal\Blog\Model\ResourceModel\Category;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'category_id';

    protected $_eventPrefix = 'baniwal_blog_category_collection';

    protected $_eventObject = 'category_collection';

    protected function _construct()
    {
        $this->_init('Baniwal\Blog\Model\Category', 'Baniwal\Blog\Model\ResourceModel\Category');
    }

    public function addAttributeToFilter($field, $condition = null)
    {
        return $this->addFieldToFilter($field, $condition);
    }

    public function addFieldToFilter($field, $condition = null)
    {
        if ($field == 'entity_id') {
            $field = 'category_id';
        }

        return parent::addFieldToFilter($field, $condition);
    }

    public function setProductStoreId($storeId)
    {
        return $this;
    }

    public function setLoadProductCount($count)
    {
        return $this;
    }

    public function setStoreId($storeId)
    {
        return $this;
    }

    public function addAttributeToSelect($attribute, $joinType = false)
    {
        return $this;
    }

    public function getSelectCountSql()
    {
        $countSelect = parent::getSelectCountSql();
        $countSelect->reset(\Zend_Db_Select::GROUP);

        return $countSelect;
    }

    protected function _toOptionArray($valueField = 'category_id', $labelField = 'name', $additional = [])
    {
        return parent::_toOptionArray($valueField, $labelField, $additional);
    }

    public function addIdFilter($categoryIds)
    {
        $condition = '';

        if (is_array($categoryIds)) {
            if (!empty($categoryIds)) {
                $condition = ['in' => $categoryIds];
            }
        } elseif (is_numeric($categoryIds)) {
            $condition = $categoryIds;
        } elseif (is_string($categoryIds)) {
            $ids = explode(',', $categoryIds);
            if (empty($ids)) {
                $condition = $categoryIds;
            } else {
                $condition = ['in' => $ids];
            }
        }

        if ($condition != '') {
            $this->addFieldToFilter('category_id', $condition);
        }

        return $this;
    }
}
