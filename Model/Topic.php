<?php
namespace Baniwal\Blog\Model;

use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Baniwal\Blog\Model\ResourceModel\Post\CollectionFactory;
use Baniwal\Blog\Model\ResourceModel\Topic\CollectionFactory as TopicCollectionFactory;

class Topic extends AbstractModel
{
    const CACHE_TAG = 'baniwal_blog_topic';

    protected $_cacheTag = 'baniwal_blog_topic';

    protected $_eventPrefix = 'baniwal_blog_topic';

    public $postCollection;

    public $postCollectionFactory;

    public $topicCollectionFactory;

    public function __construct(
        Context $context,
        Registry $registry,
        CollectionFactory $postCollectionFactory,
        TopicCollectionFactory $topicCollectionFactory,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    )
    {
        $this->postCollectionFactory = $postCollectionFactory;
        $this->topicCollectionFactory = $topicCollectionFactory;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    protected function _construct()
    {
        $this->_init('Baniwal\Blog\Model\ResourceModel\Topic');
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    public function getDefaultValues()
    {
        $values = [];
        $values['enabled'] = '1';
        $values['store_ids'] = '1';

        return $values;
    }

    public function getPostsPosition()
    {
        if (!$this->getId()) {
            return [];
        }
        $array = $this->getData('posts_position');
        if ($array === null) {
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
                ['topic' => $this->getResource()->getTable('baniwal_blog_post_topic')],
                'main_table.post_id=topic.post_id AND topic.topic_id=' . $this->getId(),
                ['position']
            );
            $this->postCollection = $collection;
        }

        return $this->postCollection;
    }
}
