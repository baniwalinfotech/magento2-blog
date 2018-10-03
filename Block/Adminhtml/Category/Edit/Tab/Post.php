<?php

namespace Baniwal\Blog\Block\Adminhtml\Category\Edit\Tab;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Backend\Helper\Data;
use Magento\Framework\Registry;
use Baniwal\Blog\Model\PostFactory;
use Baniwal\Blog\Model\ResourceModel\Post\CollectionFactory;

class Post extends Extended implements TabInterface
{
    public $postCollectionFactory;

    public $coreRegistry;

    public $postFactory;

    public function __construct(
        Context $context,
        Registry $coreRegistry,
        Data $backendHelper,
        PostFactory $postFactory,
        CollectionFactory $postCollectionFactory,
        array $data = []
    )
    {
        $this->postCollectionFactory = $postCollectionFactory;
        $this->coreRegistry = $coreRegistry;
        $this->postFactory = $postFactory;

        parent::__construct($context, $backendHelper, $data);
    }

    public function _construct()
    {
        parent::_construct();
        $this->setId('post_grid');
        $this->setDefaultSort('position');
        $this->setDefaultDir('ASC');
        $this->setUseAjax(true);

        if ($this->getCategory()->getId()) {
            $this->setDefaultFilter(['in_posts' => 1]);
        }
    }

    protected function _prepareCollection()
    {
        $collection = $this->postCollectionFactory->create();
        $collection->getSelect()->joinLeft(
            ['related' => $collection->getTable('baniwal_blog_post_category')],
            'related.post_id=main_table.post_id AND related.category_id=' . (int)$this->getRequest()->getParam('id', 0),
            ['position']
        );

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('in_posts', [
                'header_css_class' => 'a-center',
                'type' => 'checkbox',
                'name' => 'in_post',
                'values' => $this->_getSelectedPosts(),
                'align' => 'center',
                'index' => 'post_id'
            ]
        );
        $this->addColumn('post_id', [
                'header' => __('ID'),
                'sortable' => true,
                'index' => 'post_id',
                'type' => 'number',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );
        $this->addColumn('title', [
                'header' => __('Name'),
                'index' => 'name',
                'header_css_class' => 'col-name',
                'column_css_class' => 'col-name'
            ]
        );
        $this->addColumn('position', [
                'header' => __('Position'),
                'name' => 'position',
                'width' => 60,
                'type' => 'number',
                'validate_class' => 'validate-number',
                'index' => 'position',
                'editable' => true,
            ]
        );

        return $this;
    }

    protected function _getSelectedPosts()
    {
        $posts = $this->getRequest()->getPost('category_posts', null);
        if (!is_array($posts)) {
            $posts = $this->getCategory()->getPostsPosition();

            return array_keys($posts);
        }

        return $posts;
    }

    public function getSelectedPosts()
    {
        $selected = $this->getCategory()->getPostsPosition();
        if (!is_array($selected)) {
            $selected = [];
        } else {
            foreach ($selected as $key => $value) {
                $selected[$key] = ['position' => $value];
            }
        }

        return $selected;
    }

    public function getRowUrl($item)
    {
        return '#';
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/postsGrid', ['id' => $this->getCategory()->getId()]);
    }

    public function getCategory()
    {
        return $this->coreRegistry->registry('category');
    }

    protected function _addColumnFilterToCollection($column)
    {
        if ($column->getId() == 'in_posts') {
            $postIds = $this->_getSelectedPosts();
            if (empty($postIds)) {
                $postIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('main_table.post_id', ['in' => $postIds]);
            } else {
                if ($postIds) {
                    $this->getCollection()->addFieldToFilter('main_table.post_id', ['nin' => $postIds]);
                }
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }

        return $this;
    }

    public function getTabLabel()
    {
        return __('Posts');
    }

    /**
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * @return string
     */
    public function getTabTitle()
    {
        return $this->getTabLabel();
    }

    /**
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * @return string
     */
    public function getTabUrl()
    {
        return $this->getUrl('baniwal_blog/category/posts', ['_current' => true]);
    }

    /**
     * @return string
     */
    public function getTabClass()
    {
        return 'ajax only';
    }
}
