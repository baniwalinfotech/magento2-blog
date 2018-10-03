<?php

namespace Baniwal\Blog\Block\Post;

use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Baniwal\Blog\Helper\Data;

class RelatedPost extends Template
{
    protected $_coreRegistry;

    protected $helperData;

    protected $_relatedPosts;

    protected $_limitPost;

    public function __construct(
        Context $context,
        Registry $registry,
        Data $helperData,
        array $data = []
    )
    {
        $this->_coreRegistry = $registry;
        $this->helperData = $helperData;

        parent::__construct($context, $data);

        $this->setTabTitle();
    }

    public function getProductId()
    {
        $product = $this->_coreRegistry->registry('product');

        return $product ? $product->getId() : null;
    }

    public function getRelatedPostList()
    {
        if ($this->_relatedPosts == null) {
            $collection = $this->helperData->getPostList();
            $collection->getSelect()
                ->join([
                    'related' => $collection->getTable('baniwal_blog_post_product')],
                    'related.post_id=main_table.post_id AND related.entity_id=' . $this->getProductId()
                )
                ->limit($this->getLimitPosts());

            $this->_relatedPosts = $collection;
        }

        return $this->_relatedPosts;
    }

    public function getLimitPosts()
    {
        if ($this->_limitPost == null) {
            $this->_limitPost = (int)$this->helperData->getBlogConfig('product_post/product_detail/post_limit') ?: 1;
        }

        return $this->_limitPost;
    }

    public function setTabTitle()
    {
        $relatedSize = min($this->getRelatedPostList()->getSize(), $this->getLimitPosts());
        $title = $relatedSize
            ? __('Related Posts %1', '<span class="counter">' . $relatedSize . '</span>')
            : __('Related Posts');

        $this->setTitle($title);
    }
}
