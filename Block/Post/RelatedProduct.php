<?php

namespace Baniwal\Blog\Block\Post;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Block\Product\ListProduct;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\Data\Helper\PostHelper;
use Magento\Framework\Url\Helper\Data;
use Baniwal\Blog\Helper\Data as HelperData;

class RelatedProduct extends ListProduct
{
    const TITLE = 'Related Products';

    const LIMIT = '12';

    protected $_productCollectionFactory;

    protected $helper;

    public function __construct(
        Context $context,
        PostHelper $postDataHelper,
        Resolver $layerResolver,
        CategoryRepositoryInterface $categoryRepository,
        CollectionFactory $productCollectionFactory,
        HelperData $helperData,
        Data $urlHelper,
        array $data = []
    )
    {
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->helper = $helperData;

        parent::__construct($context, $postDataHelper, $layerResolver, $categoryRepository, $urlHelper, $data);
    }

    public function hasProduct()
    {
        $collection = $this->_getProductCollection();

        return $collection->getSize();
    }

    public function _getProductCollection()
    {
        if ($this->_productCollection === null) {
            $postId = $this->getRequest()->getParam('id');
            $collection = $this->_productCollectionFactory->create()
                ->addAttributeToSelect('*')
                ->addStoreFilter();

            $collection->getSelect()
                ->join(
                    ['product_post' => $collection->getTable('baniwal_blog_post_product')],
                    "e.entity_id = product_post.entity_id"
                )
                ->where('product_post.post_id = ' . $postId)
                ->order('product_post.position ASC')
                ->limit((int)$this->helper->getBlogConfig('product_post/post_detail/product_limit') ?: self::LIMIT);

            $this->_productCollection = $collection;
        }

        return $this->_productCollection;
    }

    public function getMode()
    {
        return 'grid';
    }

    public function getToolbarHtml()
    {
        return null;
    }

    public function getAdditionalHtml()
    {
        return null;
    }

    protected function _beforeToHtml()
    {
        return $this;
    }
}
