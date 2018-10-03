<?php

namespace Baniwal\Blog\Model\ResourceModel;

use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Baniwal\Blog\Helper\Data;

class Post extends AbstractDb
{
    public $date;

    public $eventManager;

    public $postTagTable;

    public $postTopicTable;

    public $postCategoryTable;

    public $postProductTable;

    public $helperData;

    public function __construct(
        Context $context,
        DateTime $date,
        ManagerInterface $eventManager,
        Data $helperData
    )
    {
        $this->date = $date;
        $this->eventManager = $eventManager;
        $this->helperData = $helperData;

        parent::__construct($context);

        $this->postTagTable = $this->getTable('baniwal_blog_post_tag');
        $this->postTopicTable = $this->getTable('baniwal_blog_post_topic');
        $this->postCategoryTable = $this->getTable('baniwal_blog_post_category');
        $this->postProductTable = $this->getTable('baniwal_blog_post_product');
    }

    protected function _construct()
    {
        $this->_init('baniwal_blog_post', 'post_id');
    }

    public function getPostNameById($id)
    {
        $adapter = $this->getConnection();
        $select = $adapter->select()
            ->from($this->getMainTable(), 'name')
            ->where('post_id = :post_id');
        $binds = ['post_id' => (int)$id];

        return $adapter->fetchOne($select, $binds);
    }

    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {

        if (is_array($object->getStoreIds())) {
            $object->setStoreIds(implode(',', $object->getStoreIds()));
        }

        $object->setUrlKey(
            $this->helperData->generateUrlKey($this, $object, $object->getUrlKey() ?: $object->getName())
        );

        return $this;
    }

    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        $this->saveTagRelation($object);
        $this->saveTopicRelation($object);
        $this->saveCategoryRelation($object);
        $this->saveProductRelation($object);

        return parent::_afterSave($object);
    }

    public function saveTagRelation(\Baniwal\Blog\Model\Post $post)
    {
        $post->setIsChangedTagList(false);
        $id = $post->getId();
        $tags = $post->getTagsIds();

        if ($tags === null) {
            return $this;
        }
        $oldTags = $post->getTagIds();

        $insert = array_diff($tags, $oldTags);
        $delete = array_diff($oldTags, $tags);

        $adapter = $this->getConnection();
        if (!empty($delete)) {
            $condition = ['tag_id IN(?)' => $delete, 'post_id=?' => $id];
            $adapter->delete($this->postTagTable, $condition);
        }
        if (!empty($insert)) {
            $data = [];
            foreach ($insert as $tagId) {
                $data[] = [
                    'post_id' => (int)$id,
                    'tag_id' => (int)$tagId,
                    'position' => 1
                ];
            }
            $adapter->insertMultiple($this->postTagTable, $data);
        }
        if (!empty($insert) || !empty($delete)) {
            $tagIds = array_unique(array_merge(array_keys($insert), array_keys($delete)));
            $this->eventManager->dispatch(
                'baniwal_blog_post_change_tags',
                ['post' => $post, 'tag_ids' => $tagIds]);
        }

        if (!empty($insert) || !empty($delete)) {
            $post->setIsChangedTagList(true);
            $tagIds = array_keys($insert + $delete);
            $post->setAffectedTagIds($tagIds);
        }

        return $this;
    }

    public function saveTopicRelation(\Baniwal\Blog\Model\Post $post)
    {
        $post->setIsChangedTopicList(false);
        $id = $post->getId();
        $topics = $post->getTopicsIds();

        if ($topics === null) {
            return $this;
        }
        $oldTopics = $post->getTopicIds();

        $insert = array_diff($topics, $oldTopics);
        $delete = array_diff($oldTopics, $topics);

        $adapter = $this->getConnection();
        if (!empty($delete)) {
            $condition = ['topic_id IN(?)' => $delete, 'post_id=?' => $id];
            $adapter->delete($this->postTopicTable, $condition);
        }
        if (!empty($insert)) {
            $data = [];
            foreach ($insert as $topicId) {
                $data[] = [
                    'post_id' => (int)$id,
                    'topic_id' => (int)$topicId,
                    'position' => 1
                ];
            }
            $adapter->insertMultiple($this->postTopicTable, $data);
        }

        if (!empty($insert) || !empty($delete)) {
            $topicIds = array_unique(array_merge(array_keys($insert), array_keys($delete)));
            $this->eventManager->dispatch(
                'baniwal_blog_post_change_topics',
                ['post' => $post, 'topic_ids' => $topicIds]
            );
        }
        if (!empty($insert) || !empty($delete)) {
            $post->setIsChangedTopicList(true);
            $topicIds = array_keys($insert + $delete);
            $post->setAffectedTopicIds($topicIds);
        }

        return $this;
    }

    public function saveCategoryRelation(\Baniwal\Blog\Model\Post $post)
    {
        $post->setIsChangedCategoryList(false);
        $id = $post->getId();
        $categories = $post->getCategoriesIds();
        if ($categories === null) {
            return $this;
        }
        $oldCategoryIds = $post->getCategoryIds();
        $insert = array_diff($categories, $oldCategoryIds);
        $delete = array_diff($oldCategoryIds, $categories);
        $adapter = $this->getConnection();


        if (!empty($delete)) {
            $condition = ['category_id IN(?)' => $delete, 'post_id=?' => $id];
            $adapter->delete($this->postCategoryTable, $condition);
        }
        if (!empty($insert)) {
            $data = [];
            foreach ($insert as $categoryId) {
                $data[] = [
                    'post_id' => (int)$id,
                    'category_id' => (int)$categoryId,
                    'position' => 1
                ];
            }
            $adapter->insertMultiple($this->postCategoryTable, $data);
        }
        if (!empty($insert) || !empty($delete)) {
            $categoryIds = array_unique(array_merge(array_keys($insert), array_keys($delete)));
            $this->eventManager->dispatch(
                'baniwal_blog_post_change_categories',
                ['post' => $post, 'category_ids' => $categoryIds]
            );
        }
        if (!empty($insert) || !empty($delete)) {
            $post->setIsChangedCategoryList(true);
            $categoryIds = array_keys($insert + $delete);
            $post->setAffectedCategoryIds($categoryIds);
        }

        return $this;
    }

    public function getCategoryIds(\Baniwal\Blog\Model\Post $post)
    {
        $adapter = $this->getConnection();
        $select = $adapter->select()->from(
            $this->postCategoryTable,
            'category_id'
        )
            ->where(
                'post_id = ?',
                (int)$post->getId()
            );

        return $adapter->fetchCol($select);
    }

    public function getTagIds(\Baniwal\Blog\Model\Post $post)
    {
        $adapter = $this->getConnection();
        $select = $adapter->select()->from(
            $this->postTagTable,
            'tag_id'
        )
            ->where(
                'post_id = ?',
                (int)$post->getId()
            );

        return $adapter->fetchCol($select);
    }

    public function getTopicIds(\Baniwal\Blog\Model\Post $post)
    {
        $adapter = $this->getConnection();
        $select = $adapter->select()->from($this->postTopicTable, 'topic_id')
            ->where('post_id = ?', (int)$post->getId());
        return $adapter->fetchCol($select);
    }

    public function saveProductRelation(\Baniwal\Blog\Model\Post $post)
    {
        $post->setIsChangedProductList(false);
        $id = $post->getId();
        $products = $post->getProductsData();
        if ($products === null) {
            return $this;
        }
        $oldProducts = $post->getProductsPosition();
        $insert = array_diff_key($products, $oldProducts);
        $delete = array_diff_key($oldProducts, $products);
        $update = array_intersect_key($products, $oldProducts);
        $_update = [];
        foreach ($update as $key => $settings) {
            if (isset($oldProducts[$key]) && $oldProducts[$key] != $settings['position']) {
                $_update[$key] = $settings;
            }
        }
        $update = $_update;
        $adapter = $this->getConnection();
        if (!empty($delete)) {
            $condition = ['entity_id IN(?)' => array_keys($delete), 'post_id=?' => $id];
            $adapter->delete($this->postProductTable, $condition);
        }
        if (!empty($insert)) {
            $data = [];
            foreach ($insert as $entityId => $position) {
                $data[] = [
                    'post_id' => (int)$id,
                    'entity_id' => (int)$entityId,
                    'position' => (int)$position['position']
                ];
            }
            $adapter->insertMultiple($this->postProductTable, $data);
        }
        if (!empty($update)) {
            foreach ($update as $entityId => $position) {
                $where = ['post_id = ?' => (int)$id, 'entity_id = ?' => (int)$entityId];
                $bind = ['position' => (int)$position['position']];
                $adapter->update($this->postProductTable, $bind, $where);
            }
        }
        if (!empty($insert) || !empty($delete)) {
            $entityIds = array_unique(array_merge(array_keys($insert), array_keys($delete)));
            $this->eventManager->dispatch(
                'baniwal_blog_post_change_products',
                ['post' => $post, 'entity_ids' => $entityIds]
            );
        }
        if (!empty($insert) || !empty($update) || !empty($delete)) {
            $post->setIsChangedProductList(true);
            $entityIds = array_keys($insert + $delete + $update);
            $post->setAffectedEntityIds($entityIds);
        }

        return $this;
    }

    public function getProductsPosition(\Baniwal\Blog\Model\Post $post)
    {
        $select = $this->getConnection()->select()->from(
            $this->postProductTable,
            ['entity_id', 'position']
        )
            ->where(
                'post_id = :post_id'
            );
        $bind = ['post_id' => (int)$post->getId()];

        return $this->getConnection()->fetchPairs($select, $bind);
    }

    public function isDuplicateUrlKey($urlKey)
    {
        $adapter = $this->getConnection();
        $select = $adapter->select()
            ->from($this->getMainTable(), 'post_id')
            ->where('url_key = :url_key');
        $binds = ['url_key' => $urlKey];

        return $adapter->fetchOne($select, $binds);
    }

    public function isImported($importSource, $oldId)
    {
        $adapter = $this->getConnection();
        $select = $adapter->select()
            ->from($this->getMainTable(), 'post_id')
            ->where('import_source = :import_source');
        $binds = ['import_source' => $importSource . '-' . $oldId];

        return $adapter->fetchOne($select, $binds);
    }

    public function deleteImportItems($importType)
    {
        $adapter = $this->getConnection();
        $adapter->delete($this->getMainTable(), "`import_source` LIKE '" . $importType . "%'");
    }
}
