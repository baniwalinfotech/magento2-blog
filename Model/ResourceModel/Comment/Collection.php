<?php

namespace Baniwal\Blog\Model\ResourceModel\Comment;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'comment_id';

    protected function _construct()
    {
        $this->_init('Baniwal\Blog\Model\Comment', 'Baniwal\Blog\Model\ResourceModel\Comment');
    }
}
