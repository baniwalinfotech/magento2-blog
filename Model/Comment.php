<?php

namespace Baniwal\Blog\Model;

use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Baniwal\Blog\Model\ResourceModel\Post\CollectionFactory;
use Baniwal\Blog\Model\ResourceModel\Comment\CollectionFactory as CommentCollectionFactory;

class Comment extends AbstractModel
{
    const CACHE_TAG = 'baniwal_blog_comment';

    protected $_cacheTag = 'baniwal_blog_comment';

    protected $_eventPrefix = 'baniwal_blog_comment';

    protected $_idFieldName = 'comment_id';

    public $postCollectionFactory;

    public $commentCollectionFactory;

    public function __construct(
        Context $context,
        Registry $registry,
        CollectionFactory $postCollectionFactory,
        CommentCollectionFactory $commentCollectionFactory,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    )
    {
        $this->postCollectionFactory = $postCollectionFactory;
        $this->commentCollectionFactory = $commentCollectionFactory;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    protected function _construct()
    {
        $this->_init('Baniwal\Blog\Model\ResourceModel\Comment');
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }
}
