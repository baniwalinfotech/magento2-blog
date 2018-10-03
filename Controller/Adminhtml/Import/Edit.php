<?php

namespace Baniwal\Blog\Controller\Adminhtml\Import;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Registry;


class Edit extends Action
{
    public $resultPageFactory;

    public $registry;

    public function __construct(
        Action\Context $context,
        PageFactory $resultPageFactory,
        Registry $registry
    )
    {
        $this->registry = $registry;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $data = $this->_session->getData('baniwal_blog_import_data');

        $this->registry->register('baniwal_blog_import', $data);

        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Baniwal_Blog::import');
        $resultPage->getConfig()->getTitle()->set(__('Import'));

        $resultPage->getConfig()->getTitle()->prepend('Import Settings');

        return $resultPage;
    }
}
