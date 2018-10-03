<?php

namespace Baniwal\Blog\Model;

use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Baniwal\Blog\Model\ResourceModel\Post\CollectionFactory;
use Baniwal\Blog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;

class Category extends AbstractModel
{
    const CACHE_TAG = 'baniwal_blog_category';

    protected $_cacheTag = 'baniwal_blog_category';

    protected $_eventPrefix = 'baniwal_blog_category';

    public $postCollection;

    public $categoryFactory;

    public $postCollectionFactory;

    public $categoryCollectionFactory;

    public function __construct(
        Context $context,
        Registry $registry,
        CategoryFactory $categoryFactory,
        CollectionFactory $postCollectionFactory,
        CategoryCollectionFactory $categoryCollectionFactory,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    )
    {
        $this->categoryFactory = $categoryFactory;
        $this->postCollectionFactory = $postCollectionFactory;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    protected function _construct()
    {
        $this->_init('Baniwal\Blog\Model\ResourceModel\Category');
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    public function getDefaultValues()
    {
        $values = [];
        $values['store_ids'] = '1';
        $values['enabled'] = '1';

        return $values;
    }

    public function getPathIds()
    {
        $ids = $this->getData('path_ids');
        if ($ids === null) {
            $ids = explode('/', $this->getPath());
            $this->setData('path_ids', $ids);
        }

        return $ids;
    }

    public function getParentIds()
    {
        return array_diff($this->getPathIds(), [$this->getId()]);
    }

    public function move($parentId, $afterCategoryId)
    {
        try {
            $parent = $this->categoryFactory->create()->load($parentId);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            throw new LocalizedException(
                __('Sorry, but we can\'t move the Blog Category because we can\'t find the new parent Blog Category you selected.'),
                $e
            );
        }

        if (!$this->getId()) {
            throw new LocalizedException(
                __('Sorry, but we can\'t move the Blog Category because we can\'t find the new parent Blog Category you selected.')
            );
        } elseif ($parent->getId() == $this->getId()) {
            throw new LocalizedException(
                __('We can\'t perform this Blog Category move operation because the parent Blog Category matches the child Blog Category.')
            );
        }

        $this->setMovedCategoryId($this->getId());
        $oldParentId = $this->getParentId();

        $eventParams = [
            $this->_eventObject => $this,
            'parent' => $parent,
            'category_id' => $this->getId(),
            'prev_parent_id' => $oldParentId,
            'parent_id' => $parentId,
        ];

        $this->_getResource()->beginTransaction();
        try {
            $this->_eventManager->dispatch($this->_eventPrefix . '_move_before', $eventParams);
            $this->getResource()->changeParent($this, $parent, $afterCategoryId);
            $this->_eventManager->dispatch($this->_eventPrefix . '_move_after', $eventParams);
            $this->_getResource()->commit();

            // Set data for indexer
            $this->setAffectedCategoryIds([$this->getId(), $oldParentId, $parentId]);
        } catch (\Exception $e) {
            $this->_getResource()->rollBack();
            throw $e;
        }
        $this->_eventManager->dispatch($this->_eventPrefix . '_move', $eventParams);
        $this->_cacheManager->clean([self::CACHE_TAG]);

        return $this;
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
        if (!$this->postCollection) {
            $collection = $this->postCollectionFactory->create();
            $collection->join(
                ['cat' => $this->getResource()->getTable('baniwal_blog_post_category')],
                'main_table.post_id=cat.post_id AND cat.category_id=' . $this->getId(),
                ['position']
            );
            $this->postCollection = $collection;
        }

        return $this->postCollection;
    }
}
