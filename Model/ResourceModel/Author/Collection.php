<?php

namespace Baniwal\Blog\Model\ResourceModel\Author;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'user_id';

    protected function _construct()
    {
        $this->_init('Baniwal\Blog\Model\Author', 'Baniwal\Blog\Model\ResourceModel\Author');
    }
}
