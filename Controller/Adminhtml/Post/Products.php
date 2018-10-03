<?php

namespace Baniwal\Blog\Controller\Adminhtml\Post;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\LayoutFactory;
use Baniwal\Blog\Controller\Adminhtml\Post;
use Baniwal\Blog\Model\PostFactory;

class Products extends Post
{
    protected $resultLayoutFactory;

    public function __construct(
        Context $context,
        Registry $registry,
        PostFactory $productFactory,
        LayoutFactory $resultLayoutFactory
    )
    {
        parent::__construct($productFactory, $registry, $context);

        $this->resultLayoutFactory = $resultLayoutFactory;
    }

    public function execute()
    {
        $this->initPost(true);

        return $this->resultLayoutFactory->create();
    }
}
