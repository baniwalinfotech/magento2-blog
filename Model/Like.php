<?php

namespace Baniwal\Blog\Model;

use Magento\Framework\Model\AbstractModel;

class Like extends AbstractModel
{
    const CACHE_TAG = 'baniwal_blog_comment_like';

    protected $_cacheTag = 'baniwal_blog_comment_like';

    protected $_eventPrefix = 'baniwal_blog_comment_like';

    protected $_idFieldName = 'like_id';

    protected function _construct()
    {
        $this->_init('Baniwal\Blog\Model\ResourceModel\Like');
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }
}
