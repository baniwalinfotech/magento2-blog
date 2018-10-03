<?php

namespace Baniwal\Blog\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Traffic extends AbstractDb
{
    /**
     * Define main table
     */
    public function _construct()
    {
        $this->_init('baniwal_blog_post_traffic', 'traffic_id');
    }
}
