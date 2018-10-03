<?php

namespace Baniwal\Blog\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Like extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('baniwal_blog_comment_like', 'like_id');
    }
}
