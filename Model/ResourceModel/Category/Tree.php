<?php

namespace Baniwal\Blog\Model\ResourceModel\Category;

use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Data\Tree\Dbp;
use Magento\Framework\DB\Select;
use Magento\Framework\Event\ManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Baniwal\Blog\Model\ResourceModel\Category;
use Baniwal\Blog\Model\ResourceModel\Category\CollectionFactory;

class Tree extends Dbp
{
    const ID_FIELD = 'id';

    const PATH_FIELD = 'path';

    const ORDER_FIELD = 'order';

    const LEVEL_FIELD = 'level';

    public $eventManager;

    public $collectionFactory;

    public $categoryResource;

    public $cache;

    public $storeManager;

    public $coreResource;

    public $collection;

    public $inactiveCategoryIds;

    public function __construct(
        ManagerInterface $eventManager,
        CollectionFactory $collectionFactory,
        Category $categoryResource,
        CacheInterface $cache,
        StoreManagerInterface $storeManager,
        ResourceConnection $coreResource
    )
    {
        $this->eventManager = $eventManager;
        $this->collectionFactory = $collectionFactory;
        $this->categoryResource = $categoryResource;
        $this->cache = $cache;
        $this->storeManager = $storeManager;
        $this->coreResource = $coreResource;

        parent::__construct(
            $coreResource->getConnection('baniwal_blog_write'),
            $coreResource->getTableName('baniwal_blog_category'),
            [
                Dbp::ID_FIELD => 'category_id',
                Dbp::PATH_FIELD => 'path',
                Dbp::ORDER_FIELD => 'position',
                Dbp::LEVEL_FIELD => 'level'
            ]
        );
    }

    public function addCollectionData(
        $collection = null,
        $sorted = false,
        $exclude = [],
        $toLoad = true,
        $onlyActive = false
    )
    {
        if ($collection === null) {
            $collection = $this->getCollection($sorted);
        } else {
            $this->setCollection($collection);
        }

        if (!is_array($exclude)) {
            $exclude = [$exclude];
        }

        $nodeIds = [];
        foreach ($this->getNodes() as $node) {
            if (!in_array($node->getId(), $exclude)) {
                $nodeIds[] = $node->getId();
            }
        }
        $collection->addIdFilter($nodeIds);
        if ($onlyActive) {
            $disabledIds = $this->getDisabledIds($collection, $nodeIds);
            if ($disabledIds) {
                $collection->addFieldToFilter('category_id', ['nin' => $disabledIds]);
            }
        }

        if ($toLoad) {
            $collection->load();

            foreach ($collection as $category) {
                if ($this->getNodeById($category->getId())) {
                    $this->getNodeById($category->getId())->addData($category->getData());
                }
            }

            foreach ($this->getNodes() as $node) {
                if (!$collection->getItemById($node->getId()) && $node->getParent()) {
                    $this->removeNode($node);
                }
            }
        }

        return $this;
    }

    public function addInactiveCategoryIds($ids)
    {
        if (!is_array($this->inactiveCategoryIds)) {
            $this->initInactiveCategoryIds();
        }
        $this->inactiveCategoryIds = array_merge($ids, $this->inactiveCategoryIds);

        return $this;
    }

    public function initInactiveCategoryIds()
    {
        $this->inactiveCategoryIds = [];
        $this->eventManager->dispatch('baniwal_blog_category_tree_init_inactive_category_ids', ['tree' => $this]);

        return $this;
    }

    public function getInactiveCategoryIds()
    {
        if (!is_array($this->inactiveCategoryIds)) {
            $this->initInactiveCategoryIds();
        }

        return $this->inactiveCategoryIds;
    }

    public function getDisabledIds($collection, $allIds)
    {
        /* implement this for frontend */
        return [];
    }

    public function getInactiveItemIds($collection, $storeId)
    {
        /* implement this for frontend */
        return [];
    }

    public function getItemIsActive($id)
    {
        //implement this for frontend
        return false;
    }

    public function getCollection($sorted = false)
    {
        if ($this->collection === null) {
            $this->collection = $this->getDefaultCollection($sorted);
        }

        return $this->collection;
    }

    public function clean($object)
    {
        if (is_array($object)) {
            foreach ($object as $obj) {
                $this->clean($obj);
            }
        }
        unset($object);
    }

    public function setCollection($collection)
    {
        if ($this->collection !== null) {
            $this->clean($this->collection);
        }
        $this->collection = $collection;

        return $this;
    }

    public function getDefaultCollection($sorted = false)
    {
        $collection = $this->collectionFactory->create();
        if ($sorted) {
            if (is_string($sorted)) {
                // $sorted is supposed to be attribute name
                $collection->addFieldToSort($sorted);
            } else {
                $collection->addFieldToSort('name');
            }
        }

        return $collection;
    }

    public function move($category, $newParent, $prevNode = null)
    {
        $this->categoryResource->move($category->getId(), $newParent->getId());
        parent::move($category, $newParent, $prevNode);

        $this->afterMove();
    }

    public function afterMove()
    {
        $this->cache->clean([\Baniwal\Blog\Model\Category::CACHE_TAG]);

        return $this;
    }

    public function loadByIds($ids, $addCollectionData = true)
    {
        $levelField = $this->_conn->quoteIdentifier('level');
        $pathField = $this->_conn->quoteIdentifier('path');
        // load first two levels, if no ids specified
        if (empty($ids)) {
            $select = $this->_conn
                ->select()
                ->from($this->_table, 'category_id')
                ->where($levelField . ' <= 2');
            $ids = $this->_conn->fetchCol($select);
        }
        if (!is_array($ids)) {
            $ids = [$ids];
        }
        foreach ($ids as $key => $id) {
            $ids[$key] = (int)$id;
        }

        // collect paths of specified IDs and prepare to collect all their parents and neighbours
        $select = $this->_conn
            ->select()
            ->from($this->_table, ['path', 'level'])
            ->where('category_id IN (?)', $ids);
        $where = [$levelField . '=0' => true];

        foreach ($this->_conn->fetchAll($select) as $item) {
            $pathIds = explode('/', $item['path']);
            $level = (int)$item['level'];
            while ($level > 0) {
                $lastId = end($pathIds);
                $lastIndex = key($lastId);
                $pathIds[$lastIndex] = '%';
                $path = implode('/', $pathIds);
                $where["{$levelField}={$level} AND {$pathField} LIKE '{$path}'"] = true;
                array_pop($pathIds);
                $level--;
            }
        }
        $where = array_keys($where);

        // get all required records
        if ($addCollectionData) {
            $select = $this->createCollectionDataSelect();
        } else {
            $select = clone $this->_select;
            $select->order($this->_orderField . ' ' . Select::SQL_ASC);
        }
        $select->where(implode(' OR ', $where));

        // get array of records and add them as nodes to the tree
        $arrNodes = $this->_conn->fetchAll($select);
        if (!$arrNodes) {
            return false;
        }
        $childrenItems = [];
        foreach ($arrNodes as $key => $nodeInfo) {
            $pathToParent = explode('/', $nodeInfo[$this->_pathField]);
            array_pop($pathToParent);
            $pathToParent = implode('/', $pathToParent);
            $childrenItems[$pathToParent][] = $nodeInfo;
        }
        $this->addChildNodes($childrenItems, '', null);

        return $this;
    }

    public function loadBreadcrumbsArray($path, $addCollectionData = true, $withRootNode = false)
    {
        $pathIds = explode('/', $path);
        if (!$withRootNode) {
            array_shift($pathIds);
        }
        $result = [];
        if (!empty($pathIds)) {
            if ($addCollectionData) {
                $select = $this->createCollectionDataSelect(false);
            } else {
                $select = clone $this->_select;
            }
            $select->where(
                'e.category_id IN(?)',
                $pathIds
            )->order(
                $this->_conn->getLengthSql('e.path') . ' ' . Select::SQL_ASC
            );
            $result = $this->_conn->fetchAll($select);
        }

        return $result;
    }

    public function createCollectionDataSelect($sorted = true)
    {
        $select = $this->getDefaultCollection($sorted ? $this->_orderField : false)->getSelect();

        return $select;
    }

    public function getExistingCategoryIdsBySpecifiedIds($ids)
    {
        if (empty($ids)) {
            return [];
        }
        if (!is_array($ids)) {
            $ids = [$ids];
        }
        $select = $this->_conn
            ->select()
            ->from($this->_table, ['category_id'])
            ->where('category_id IN (?)', $ids);

        return $this->_conn->fetchCol($select);
    }
}
