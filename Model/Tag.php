<?php

namespace Baniwal\Blog\Model;

use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Baniwal\Blog\Model\ResourceModel\Post\CollectionFactory;
use Baniwal\Blog\Model\ResourceModel\Tag\CollectionFactory as TagCollectionFactory;

class Tag extends AbstractModel
{
    const CACHE_TAG = 'baniwal_blog_tag';

    protected $_cacheTag = 'baniwal_blog_tag';

    protected $_eventPrefix = 'baniwal_blog_tag';

    public $postCollection;

    public $postCollectionFactory;

    public $tagCollectionFactory;

    public function __construct(
        Context $context,
        Registry $registry,
        CollectionFactory $postCollectionFactory,
        TagCollectionFactory $tagCollectionFactory,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    )
    {
        $this->postCollectionFactory = $postCollectionFactory;
        $this->tagCollectionFactory = $tagCollectionFactory;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    protected function _construct()
    {
        $this->_init('Baniwal\Blog\Model\ResourceModel\Tag');
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    public function getPostsPosition()
    {
        if (!$this->getId()) {
            return [];
        }

        $array = $this->getData('posts_position');
        if (!$array) {
            $array = $this->getResource()->getPostsPosition($this);
            $this->setData('posts_position', $array);
        }

        return $array;
    }

    public function getSelectedPostsCollection()
    {
        if ($this->postCollection === null) {
            $collection = $this->postCollectionFactory->create();
            $collection->join(
                ['post_tag' => $this->getResource()->getTable('baniwal_blog_post_tag')],
                'main_table.post_id=post_tag.post_id AND post_tag.tag_id=' . $this->getId(),
                ['position']
            );

            $this->postCollection = $collection;
        }

        return $this->postCollection;
    }
}
