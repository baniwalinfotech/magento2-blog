<?php

namespace Baniwal\Blog\Controller\Adminhtml\Author;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Baniwal\Blog\Controller\Adminhtml\Author;
use Baniwal\Blog\Model\AuthorFactory;

class Edit extends Author
{
    public $resultPageFactory;

    public function __construct(
        Context $context,
        Registry $registry,
        AuthorFactory $authorFactory,
        PageFactory $resultPageFactory
    )
    {
        $this->resultPageFactory = $resultPageFactory;

        parent::__construct($context, $registry, $authorFactory);
    }

    public function execute()
    {
        $author = $this->initAuthor();

        //Set entered data if was error when we do save
        $data = $this->_session->getData('baniwal_blog_author_data', true);
        if (!empty($data)) {
            $author->addData($data);
        }

        $this->coreRegistry->register('baniwal_blog_author', $author);

        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Baniwal_Blog::author');
        $resultPage->getConfig()->getTitle()->set(__('Author Management'));

        $resultPage->getConfig()->getTitle()->prepend($this->_auth->getUser()->getName());

        return $resultPage;
    }
}
