<?php

namespace Baniwal\Blog\Model\ResourceModel\Topic;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'topic_id';

    protected $_eventPrefix = 'baniwal_blog_topic_collection';

    protected $_eventObject = 'topic_collection';

    protected function _construct()
    {
        $this->_init('Baniwal\Blog\Model\Topic', 'Baniwal\Blog\Model\ResourceModel\Topic');
    }

    public function getSelectCountSql()
    {
        $countSelect = parent::getSelectCountSql();
        $countSelect->reset(\Zend_Db_Select::GROUP);

        return $countSelect;
    }

    protected function _toOptionArray($valueField = 'topic_id', $labelField = 'name', $additional = [])
    {
        return parent::_toOptionArray($valueField, $labelField, $additional);
    }

    public function addIdFilter($topicIds)
    {
        $condition = '';

        if (is_array($topicIds)) {
            if (!empty($topicIds)) {
                $condition = ['in' => $topicIds];
            }
        } elseif (is_numeric($topicIds)) {
            $condition = $topicIds;
        } elseif (is_string($topicIds)) {
            $ids = explode(',', $topicIds);
            if (empty($ids)) {
                $condition = $topicIds;
            } else {
                $condition = ['in' => $ids];
            }
        }

        if ($condition != '') {
            $this->addFieldToFilter('topic_id', $condition);
        }

        return $this;
    }
}
