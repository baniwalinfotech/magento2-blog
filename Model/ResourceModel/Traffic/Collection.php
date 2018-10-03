<?php

namespace Baniwal\Blog\Model\ResourceModel\Traffic;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Baniwal\Blog\Model\Traffic', 'Baniwal\Blog\Model\ResourceModel\Traffic');
    }
}
