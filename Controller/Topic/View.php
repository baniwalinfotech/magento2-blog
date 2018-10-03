<?php

namespace Baniwal\Blog\Controller\Topic;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\View\Result\PageFactory;
use Baniwal\Blog\Helper\Data as HelperBlog;

class View extends Action
{
    public $resultPageFactory;

    protected $resultForwardFactory;

    public $helperBlog;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        ForwardFactory $resultForwardFactory,
        HelperBlog $helperBlog
    )
    {
        parent::__construct($context);

        $this->resultPageFactory = $resultPageFactory;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->helperBlog = $helperBlog;
    }

    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $topic = $this->helperBlog->getFactoryByType(HelperBlog::TYPE_TOPIC)->create()->load($id);

        return ($topic->getEnabled()) ? $this->resultPageFactory->create() : $this->resultForwardFactory->create()->forward('noroute');
    }
}
