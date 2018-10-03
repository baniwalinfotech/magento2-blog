<?php

namespace Baniwal\Blog\Model\ResourceModel\Like;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Baniwal\Blog\Model\Like', 'Baniwal\Blog\Model\ResourceModel\Like');
    }
}
