<?php

namespace Baniwal\Blog\Model\ResourceModel;

use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Baniwal\Blog\Helper\Data;

class Topic extends AbstractDb
{
    public $date;

    public $eventManager;

    public $topicPostTable;

    public $helperData;

    public function __construct(
        Context $context,
        DateTime $date,
        ManagerInterface $eventManager,
        Data $helperData
    )
    {
        $this->helperData = $helperData;
        $this->date = $date;
        $this->eventManager = $eventManager;

        parent::__construct($context);

        $this->topicPostTable = $this->getTable('baniwal_blog_post_topic');
    }

    protected function _construct()
    {
        $this->_init('baniwal_blog_topic', 'topic_id');
    }

    public function getTopicNameById($id)
    {
        $adapter = $this->getConnection();
        $select = $adapter->select()
            ->from($this->getMainTable(), 'name')
            ->where('topic_id = :topic_id');
        $binds = ['topic_id' => (int)$id];

        return $adapter->fetchOne($select, $binds);
    }

    protected function _beforeSave(AbstractModel $object)
    {
        $object->setUpdatedAt($this->date->date());
        if ($object->isObjectNew()) {
            $object->setCreatedAt($this->date->date());
        }

        if (is_array($object->getStoreIds())) {
            $object->setStoreIds(implode(',', $object->getStoreIds()));
        }

        $object->setUrlKey(
            $this->helperData->generateUrlKey($this, $object, $object->getUrlKey() ?: $object->getName())
        );

        return parent::_beforeSave($object);
    }

    protected function _afterSave(AbstractModel $object)
    {
        $this->savePostRelation($object);

        return parent::_afterSave($object);
    }

    public function getPostsPosition(\Baniwal\Blog\Model\Topic $topic)
    {
        $select = $this->getConnection()->select()->from(
            $this->topicPostTable,
            ['post_id', 'position']
        )
            ->where(
                'topic_id = :topic_id'
            );
        $bind = ['topic_id' => (int)$topic->getId()];

        return $this->getConnection()->fetchPairs($select, $bind);
    }

    protected function savePostRelation(\Baniwal\Blog\Model\Topic $topic)
    {
        $topic->setIsChangedPostList(false);
        $id = $topic->getId();
        $posts = $topic->getPostsData();
        if ($posts === null) {
            return $this;
        }
        $oldPosts = $topic->getPostsPosition();
        $insert = array_diff_key($posts, $oldPosts);
        $delete = array_diff_key($oldPosts, $posts);
        $update = array_intersect_key($posts, $oldPosts);
        $_update = [];
        foreach ($update as $key => $settings) {
            if (isset($oldPosts[$key]) && $oldPosts[$key] != $settings['position']) {
                $_update[$key] = $settings;
            }
        }
        $update = $_update;
        $adapter = $this->getConnection();
        if (!empty($delete)) {
            $condition = ['post_id IN(?)' => array_keys($delete), 'topic_id=?' => $id];
            $adapter->delete($this->topicPostTable, $condition);
        }
        if (!empty($insert)) {
            $data = [];
            foreach ($insert as $postId => $position) {
                $data[] = [
                    'topic_id' => (int)$id,
                    'post_id' => (int)$postId,
                    'position' => (int)$position['position']
                ];
            }
            $adapter->insertMultiple($this->topicPostTable, $data);
        }
        if (!empty($update)) {
            foreach ($update as $postId => $position) {
                $where = ['topic_id = ?' => (int)$id, 'post_id = ?' => (int)$postId];
                $bind = ['position' => (int)$position['position']];
                $adapter->update($this->topicPostTable, $bind, $where);
            }
        }
        if (!empty($insert) || !empty($delete)) {
            $postIds = array_unique(array_merge(array_keys($insert), array_keys($delete)));
            $this->eventManager->dispatch(
                'baniwal_blog_topic_change_posts',
                ['topic' => $topic, 'post_ids' => $postIds]
            );
        }
        if (!empty($insert) || !empty($update) || !empty($delete)) {
            $topic->setIsChangedPostList(true);
            $postIds = array_keys($insert + $delete + $update);
            $topic->setAffectedPostIds($postIds);
        }

        return $this;
    }

    public function isImported($importSource, $oldId)
    {
        $adapter = $this->getConnection();
        $select = $adapter->select()
            ->from($this->getMainTable(), 'topic_id')
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
