<?php

namespace Baniwal\Blog\Model;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime;
use Baniwal\Blog\Helper\Data;
use Baniwal\Blog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Baniwal\Blog\Model\ResourceModel\Post\CollectionFactory as PostCollectionFactory;
use Baniwal\Blog\Model\ResourceModel\Tag\CollectionFactory;
use Baniwal\Blog\Model\ResourceModel\Topic\CollectionFactory as TopicCollectionFactory;

class Post extends AbstractModel
{
    const CACHE_TAG = 'baniwal_blog_post';

    protected $_cacheTag = 'baniwal_blog_post';

    protected $_eventPrefix = 'baniwal_blog_post';

    public $tagCollection;

    public $topicCollection;

    public $categoryCollection;

    public $tagCollectionFactory;

    public $topicCollectionFactory;

    public $categoryCollectionFactory;

    public $postCollectionFactory;

    public $relatedPostCollection;

    public $prevPostCollection;

    public $nextPostCollection;

    public $dateTime;

    public $helperData;

    public $productCollectionFactory;

    public $productCollection;

    protected $trafficFactory;

    public function __construct(
        Context $context,
        Registry $registry,
        DateTime $dateTime,
        Data $helperData,
        TrafficFactory $trafficFactory,
        CollectionFactory $tagCollectionFactory,
        TopicCollectionFactory $topicCollectionFactory,
        CategoryCollectionFactory $categoryCollectionFactory,
        PostCollectionFactory $postCollectionFactory,
        ProductCollectionFactory $productCollectionFactory,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    )
    {
        $this->tagCollectionFactory = $tagCollectionFactory;
        $this->topicCollectionFactory = $topicCollectionFactory;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->postCollectionFactory = $postCollectionFactory;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->helperData = $helperData;
        $this->dateTime = $dateTime;
        $this->trafficFactory = $trafficFactory;

        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    protected function _construct()
    {
        $this->_init('Baniwal\Blog\Model\ResourceModel\Post');
    }

    /**
     * @inheritdoc
     */
    public function afterSave()
    {
        if ($this->isObjectNew()) {
            $trafficModel = $this->trafficFactory->create()
                ->load($this->getId(), 'post_id');
            if (!$trafficModel->getId()) {
                $trafficModel->setData([
                    'post_id' => $this->getId(),
                    'numbers_view' => 0
                ])->save();
            }
        }

        return parent::afterSave();
    }

    /**
     * @param bool $shorten
     * @return mixed|string
     */
    public function getShortDescription($shorten = false)
    {
        $shortDescription = $this->getData('short_description');

        $maxLength = 200;
        if ($shorten && strlen($shortDescription) > $maxLength) {
            $shortDescription = substr($shortDescription, 0, $maxLength) . '...';
        }

        return $shortDescription;
    }

    /**
     * @return bool|string
     */
    public function getUrl()
    {
        return $this->helperData->getBlogUrl($this, Data::TYPE_POST);
    }

    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * get entity default values
     *
     * @return array
     */
    public function getDefaultValues()
    {
        $values = [];
        $values['in_rss'] = '1';
        $values['enabled'] = '1';
        $values['allow_comment'] = '1';
        $values['store_ids'] = '1';

        return $values;
    }

    public function getSelectedTagsCollection()
    {
        if ($this->tagCollection === null) {
            $collection = $this->tagCollectionFactory->create();
            $collection->getSelect()->join(
                $this->getResource()->getTable('baniwal_blog_post_tag'),
                'main_table.tag_id=' . $this->getResource()->getTable('baniwal_blog_post_tag') . '.tag_id AND ' . $this->getResource()->getTable('baniwal_blog_post_tag') . '.post_id='
                . $this->getId(),
                ['position']
            )->where("main_table.enabled='1'");
            $this->tagCollection = $collection;
        }

        return $this->tagCollection;
    }

    public function getSelectedTopicsCollection()
    {
        if ($this->topicCollection === null) {
            $collection = $this->topicCollectionFactory->create();
            $collection->join(
                $this->getResource()->getTable('baniwal_blog_post_topic'),
                'main_table.topic_id=' . $this->getResource()->getTable('baniwal_blog_post_topic') . '.topic_id AND ' . $this->getResource()->getTable('baniwal_blog_post_topic') . '.post_id='
                . $this->getId(),
                ['position']
            );
            $this->topicCollection = $collection;
        }

        return $this->topicCollection;
    }

    public function getSelectedCategoriesCollection()
    {
        if ($this->categoryCollection === null) {
            $collection = $this->categoryCollectionFactory->create();
            $collection->join(
                $this->getResource()->getTable('baniwal_blog_post_category'),
                'main_table.category_id=' . $this->getResource()->getTable('baniwal_blog_post_category') . '.category_id 
                AND ' . $this->getResource()->getTable('baniwal_blog_post_category') . '.post_id="' . $this->getId() . '"',
                ['position']
            );
            $this->categoryCollection = $collection;
        }

        return $this->categoryCollection;
    }

    public function getCategoryIds()
    {
        if (!$this->hasData('category_ids')) {
            $ids = $this->_getResource()->getCategoryIds($this);
            $this->setData('category_ids', $ids);
        }

        return (array)$this->_getData('category_ids');
    }

    public function getTagIds()
    {
        if (!$this->hasData('tag_ids')) {
            $ids = $this->_getResource()->getTagIds($this);

            $this->setData('tag_ids', $ids);
        }

        return (array)$this->_getData('tag_ids');
    }

    public function getTopicIds()
    {
        if (!$this->hasData('topic_ids')) {
            $ids = $this->_getResource()->getTopicIds($this);

            $this->setData('topic_ids', $ids);
        }
        return (array)$this->_getData('topic_ids');
    }

    public function getRelatedPostsCollection($limit = null)
    {
        $topicIds = $this->_getResource()->getTopicIds($this);
        if (sizeof($topicIds)) {
            $collection = $this->postCollectionFactory->create();
            $collection->getSelect()
                ->join(
                    ['topic' => $this->getResource()->getTable('baniwal_blog_post_topic')],
                    'main_table.post_id=topic.post_id AND topic.post_id != "' . $this->getId() . '" AND topic.topic_id IN (' . implode(',', $topicIds) . ')',
                    ['position']
                )->group('main_table.post_id');

            if ($limit = (int)$this->helperData->getBlogConfig('general/related_post')) {
                $collection->getSelect()
                    ->limit($limit);
            }

            return $collection;
        }

        return null;
    }

    public function getSelectedProductsCollection()
    {
        if ($this->productCollection === null) {
            $collection = $this->productCollectionFactory->create();
            $collection->getSelect()->join(
                $this->getResource()->getTable('baniwal_blog_post_product'),
                'main_table.entity_id=' . $this->getResource()->getTable('baniwal_blog_post_product') . '.entity_id AND ' . $this->getResource()->getTable('baniwal_blog_post_product') . '.post_id='
                . $this->getId(),
                ['position']
            )->where("main_table.enabled='1'");
            $this->productCollection = $collection;
        }

        return $this->productCollection;
    }

    public function getProductsPosition()
    {
        if (!$this->getId()) {
            return [];
        }
        $array = $this->getData('products_position');
        if ($array === null) {
            $array = $this->getResource()->getProductsPosition($this);
            $this->setData('products_position', $array);
        }

        return $array;
    }

    public function getPrevPost()
    {
        if ($this->prevPostCollection === null) {
            $collection = $this->postCollectionFactory->create();
            $collection->addFieldToFilter('post_id', ['lt' => $this->getId()])->setOrder('post_id', 'DESC')->setPageSize(1)->setCurPage(1);
            $this->prevPostCollection = $collection;
        }

        return $this->prevPostCollection;
    }

    public function getNextPost()
    {
        if ($this->nextPostCollection === null) {
            $collection = $this->postCollectionFactory->create();
            $collection->addFieldToFilter('post_id', ['gt' => $this->getId()])->setOrder('post_id', 'ASC')->setPageSize(1)->setCurPage(1);
            $this->nextPostCollection = $collection;
        }

        return $this->nextPostCollection;
    }
}
