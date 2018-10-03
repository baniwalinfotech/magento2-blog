<?php

namespace Baniwal\Blog\Model;

use Magento\Framework\Model\AbstractModel;

class Traffic extends AbstractModel
{
    public function _construct()
    {
        $this->_init('Baniwal\Blog\Model\ResourceModel\Traffic');
    }
}
