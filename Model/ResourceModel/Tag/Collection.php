<?php

namespace Baniwal\Blog\Model\ResourceModel\Tag;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'tag_id';

    protected $_eventPrefix = 'baniwal_blog_tag_collection';

    protected $_eventObject = 'tag_collection';

    protected function _construct()
    {
        $this->_init('Baniwal\Blog\Model\Tag', 'Baniwal\Blog\Model\ResourceModel\Tag');
    }

    public function getSelectCountSql()
    {
        $countSelect = parent::getSelectCountSql();
        $countSelect->reset(\Zend_Db_Select::GROUP);

        return $countSelect;
    }

    protected function _toOptionArray($valueField = 'tag_id', $labelField = 'name', $additional = [])
    {
        return parent::_toOptionArray($valueField, $labelField, $additional);
    }

    public function addIdFilter($tagIds)
    {
        $condition = '';

        if (is_array($tagIds)) {
            if (!empty($tagIds)) {
                $condition = ['in' => $tagIds];
            }
        } elseif (is_numeric($tagIds)) {
            $condition = $tagIds;
        } elseif (is_string($tagIds)) {
            $ids = explode(',', $tagIds);
            if (empty($ids)) {
                $condition = $tagIds;
            } else {
                $condition = ['in' => $ids];
            }
        }

        if ($condition != '') {
            $this->addFieldToFilter('tag_id', $condition);
        }

        return $this;
    }
}
