<?php

namespace Baniwal\Blog\Controller\Adminhtml\Tag;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\LayoutFactory;
use Baniwal\Blog\Controller\Adminhtml\Tag;
use Baniwal\Blog\Model\TagFactory;

class Posts extends Tag
{
    public $resultLayoutFactory;

    public function __construct(
        Context $context,
        Registry $registry,
        LayoutFactory $resultLayoutFactory,
        TagFactory $postFactory
    )
    {
        $this->resultLayoutFactory = $resultLayoutFactory;

        parent::__construct($context, $registry, $postFactory);
    }

    public function execute()
    {
        $this->initTag(true);

        return $this->resultLayoutFactory->create();
    }
}
