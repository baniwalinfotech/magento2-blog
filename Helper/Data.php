<?php

namespace Baniwal\Blog\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filter\TranslitUrl;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Baniwal\Blog\Model\AuthorFactory;
use Baniwal\Blog\Model\CategoryFactory;
use Baniwal\Blog\Model\PostFactory;
use Baniwal\Blog\Model\TagFactory;
use Baniwal\Blog\Model\TopicFactory;
use Baniwal\Blog\Helper\AbstractData as CoreHelper;

class Data extends CoreHelper
{
    const CONFIG_MODULE_PATH = 'blog';

    const TYPE_POST = 'post';
    const TYPE_CATEGORY = 'category';
    const TYPE_TAG = 'tag';
    const TYPE_TOPIC = 'topic';
    const TYPE_AUTHOR = 'author';
    const TYPE_MONTHLY = 'month';

    public $postFactory;

    public $categoryFactory;

    public $tagFactory;

    public $topicFactory;

    public $authorFactory;

    public $translitUrl;

    public $dateTime;

    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        StoreManagerInterface $storeManager,
        PostFactory $postFactory,
        CategoryFactory $categoryFactory,
        TagFactory $tagFactory,
        TopicFactory $topicFactory,
        AuthorFactory $authorFactory,
        TranslitUrl $translitUrl,
        DateTime $dateTime
    )
    {
        $this->postFactory = $postFactory;
        $this->categoryFactory = $categoryFactory;
        $this->tagFactory = $tagFactory;
        $this->topicFactory = $topicFactory;
        $this->authorFactory = $authorFactory;
        $this->translitUrl = $translitUrl;
        $this->dateTime = $dateTime;

        parent::__construct($context, $objectManager, $storeManager);
    }

    public function getImageHelper()
    {
        return $this->objectManager->get(Image::class);
    }

    public function getBlogConfig($code, $storeId = null)
    {
        $code = ($code !== '') ? '/' . $code : '';

        return $this->getConfigValue(self::CONFIG_MODULE_PATH . $code, $storeId);
    }

    public function getSeoConfig($code, $storeId = null)
    {
        return $this->getBlogConfig('seo/' . $code, $storeId);
    }

    public function showAuthorInfo()
    {
        return $this->getConfigGeneral('display_author');
    }

    public function getBlogName($store = null)
    {
        return $this->getConfigGeneral('name', $store) ?: 'Blog';
    }

    public function getRoute($store = null)
    {
        return $this->getConfigGeneral('url_prefix', $store) ?: 'blog';
    }

    public function getUrlSuffix($store = null)
    {
        return $this->getConfigGeneral('url_suffix', $store) ?: '';
    }

    public function getPostCollection($type = null, $id = null, $storeId = null)
    {
        if (is_null($id)) {
            $id = $this->_request->getParam('id');
        }

        $collection = $this->getPostList();

        switch ($type) {
            case self::TYPE_AUTHOR:
                $collection->addFieldToFilter('author_id', $id);
                break;
            case self::TYPE_CATEGORY:
                $collection->join(
                    ['category' => $collection->getTable('baniwal_blog_post_category')],
                    'main_table.post_id=category.post_id AND category.category_id=' . $id,
                    ['position']
                );
                break;
            case self::TYPE_TAG:
                $collection->join(
                    ['tag' => $collection->getTable('baniwal_blog_post_tag')],
                    'main_table.post_id=tag.post_id AND tag.tag_id=' . $id,
                    ['position']
                );
                break;
            case self::TYPE_TOPIC:
                $collection->join(
                    ['topic' => $collection->getTable('baniwal_blog_post_topic')],
                    'main_table.post_id=topic.post_id AND topic.topic_id=' . $id,
                    ['position']
                );
                break;
            case self::TYPE_MONTHLY:
                $collection->addFieldToFilter('publish_date', ['like' => $id . '%']);
                break;
            default:
                break;
        }

        return $collection;
    }

    public function getPostList($storeId = null)
    {
        $collection = $this->getObjectList(self::TYPE_POST, $storeId)
            ->addFieldToFilter('publish_date', ["to" => $this->dateTime->date()])
            ->setOrder('publish_date', 'desc');

        return $collection;
    }

    public function getCategoryCollection($array)
    {
        $collection = $this->getObjectList(self::TYPE_CATEGORY)
            ->addFieldToFilter('category_id', ['in' => $array]);

        return $collection;
    }

    public function getObjectList($type = null, $storeId = null)
    {
        $collection = $this->getFactoryByType($type)
            ->create()
            ->getCollection()
            ->addFieldToFilter('enabled', 1);

        $this->addStoreFilter($collection, $storeId);

        return $collection;
    }

    public function addStoreFilter($collection, $storeId = null)
    {
        if (is_null($storeId)) {
            $storeId = $this->storeManager->getStore()->getId();
        }

        $collection->addFieldToFilter('store_ids', [
            ['finset' => Store::DEFAULT_STORE_ID],
            ['finset' => $storeId]
        ]);

        return $collection;
    }

    public function getAuthorByPost($post, $modify = false)
    {
        $author = $this->authorFactory->create();

        $authorId = $modify ? $post->getModifierId() : $post->getAuthorId();
        if ($authorId) {
            $author->load($authorId);
        }

        return $author;
    }

    public function getBlogUrl($urlKey = null, $type = null)
    {
        if (is_object($urlKey)) {
            $urlKey = $urlKey->getUrlKey();
        }

        $urlKey = ($type ? $type . '/' : '') . $urlKey;
        $url = $this->getUrl($this->getRoute() . '/' . $urlKey);
        $url = explode('?', $url);
        $url = $url[0];
        return rtrim($url, '/') . $this->getUrlSuffix();
    }

    public function getObjectByParam($value, $code = null, $type = null)
    {
        $object = $this->getFactoryByType($type)
            ->create()
            ->load($value, $code);

        return $object;
    }

    public function getFactoryByType($type = null)
    {
        switch ($type) {
            case self::TYPE_CATEGORY:
                $object = $this->categoryFactory;
                break;
            case self::TYPE_TAG:
                $object = $this->tagFactory;
                break;
            case self::TYPE_AUTHOR:
                $object = $this->authorFactory;
                break;
            case self::TYPE_TOPIC:
                $object = $this->topicFactory;
                break;
            default:
                $object = $this->postFactory;
        }

        return $object;
    }

    public function generateUrlKey($resource, $object, $name)
    {
        $attempt = -1;
        do {
            if ($attempt++ >= 10) {
                throw new LocalizedException(__('Unable to generate url key. Please check the setting and try again.'));
            }

            $urlKey = $this->translitUrl->filter($name);
            if ($urlKey) {
                $urlKey = $urlKey . ($attempt ?: '');
            }
        } while ($this->checkUrlKey($resource, $object, $urlKey));

        return $urlKey;
    }

    public function checkUrlKey($resource, $object, $urlKey)
    {
        if (empty($urlKey)) {
            return true;
        }

        $adapter = $resource->getConnection();
        $select = $adapter->select()
            ->from($resource->getMainTable(), '*')
            ->where('url_key = :url_key');

        $binds = ['url_key' => (string)$urlKey];

        if ($id = $object->getId()) {
            $select->where($resource->getIdFieldName() . ' != :object_id');
            $binds['object_id'] = (int)$id;
        }

        $result = $adapter->fetchOne($select, $binds);

        return $result;
    }

    public function getDateFormat($date, $monthly = false)
    {
        $dateTime = (new \DateTime($date, new \DateTimeZone('UTC')));
        $dateTime->setTimezone(new \DateTimeZone($this->getTimezone()));

        $dateType = $this->getBlogConfig($monthly ? 'monthly_archive/date_type_monthly' : 'general/date_type');
        $dateFormat = $dateTime->format($dateType);

        return $dateFormat;
    }

    public function getTimezone()
    {
        return $this->getConfigValue('general/locale/timezone');
    }

    public function getUrl($route, $params = [])
    {
        return $this->_urlBuilder->getUrl($route, $params);
    }
}
