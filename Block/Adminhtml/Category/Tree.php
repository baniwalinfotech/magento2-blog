<?php

namespace Baniwal\Blog\Block\Adminhtml\Category;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Model\Auth\Session;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\CategoryFactory as CatalogCategoryFactory;
use Magento\Catalog\Model\ResourceModel\Category\Tree as TreeResource;
use Magento\Framework\Data\Tree\Node;
use Magento\Framework\DB\Helper;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Registry;
use Baniwal\Blog\Model\CategoryFactory;
use Baniwal\Blog\Model\ResourceModel\Category\Tree as BlogTreeResource;

class Tree extends \Magento\Catalog\Block\Adminhtml\Category\Tree
{
    protected $_blogStore;

    public function __construct(
        Context $context,
        TreeResource $categoryTree,
        Registry $registry,
        CatalogCategoryFactory $categoryFactory,
        EncoderInterface $jsonEncoder,
        Helper $resourceHelper,
        Session $backendSession,
        BlogTreeResource $blogCategoryTree,
        CategoryFactory $blogCategoryFactory,
        array $data = []
    )
    {
        parent::__construct($context, $categoryTree, $registry, $categoryFactory, $jsonEncoder, $resourceHelper, $backendSession, $data);

        $this->_categoryTree = $blogCategoryTree;
        $this->_categoryFactory = $blogCategoryFactory;
        $this->_withProductCount = false;
    }

    /**
     * @return string
     */
    public function getNodesUrl()
    {
        return $this->getUrl('baniwal_blog/category/jsonTree');
    }

    /**
     * @return string
     */
    public function getMoveUrl()
    {
        return $this->getUrl('baniwal_blog/category/move');
    }

    /**
     * @param array $args
     * @return string
     */
    public function getSaveUrl(array $args = [])
    {
        $params = ['_current' => false, '_query' => false];
        $params = array_merge($params, $args);

        return $this->getUrl('baniwal_blog/*/save', $params);
    }

    /**
     * @return string
     */
    public function getEditUrl()
    {
        return $this->getUrl(
            'baniwal_blog/category/edit',
            ['store' => null, '_query' => false, 'id' => null, 'parent' => null]
        );
    }

    /**
     * @param null $parentNodeCategory
     * @param null $store
     * @return array
     */
    public function getTree($parentNodeCategory = null, $store = null)
    {
        $this->_blogStore = $store;

        return parent::getTree($parentNodeCategory);
    }

    /**
     * Get JSON of a tree node or an associative array
     *
     * @param \Magento\Framework\Data\Tree\Node|array $node
     * @param int $level
     * @return string
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _getNodeJson($node, $level = 0)
    {
        // create a node from data array
        if (is_array($node)) {
            $node = new Node($node, 'category_id', new \Magento\Framework\Data\Tree());
        }

        $storeIds = $node->getStoreIds() ? explode(',', $node->getStoreIds()) : [];
        if (!empty($storeIds) && !in_array(0, $storeIds) && !is_null($this->_blogStore) && !in_array($this->_blogStore, $storeIds)) {
            return null;
        }

        $node->setIsActive(true);

        if ($item = parent::_getNodeJson($node, $level)) {
            $item['url'] = $node->getData('url_key');
            $item['storeIds'] = $node->getData('store_ids');
            $item['allowDrag'] = $this->_isCategoryMoveable($node) && ($node->getLevel() == 0 ? false : true);
            $item['enabled'] = $node->getData('enabled');

            return $item;
        }

        return null;
    }

    /**
     * Return ids of root categories as array
     *
     * @return array
     */
    public function getRootIds()
    {
        $ids = $this->getData('root_ids');
        if ($ids === null) {
            $ids = [Category::TREE_ROOT_ID];
            $this->setData('root_ids', $ids);
        }

        return $ids;
    }
}
