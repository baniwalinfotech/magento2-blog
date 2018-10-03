<?php

namespace Baniwal\Blog\Controller\Author;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\View\Result\PageFactory;

class View extends Action
{
    public $resultPageFactory;

    protected $resultForwardFactory;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        ForwardFactory $resultForwardFactory
    )
    {
        parent::__construct($context);

        $this->resultPageFactory = $resultPageFactory;
        $this->resultForwardFactory = $resultForwardFactory;
    }

    public function execute()
    {
        $id = $this->getRequest()->getParam('id');

        return ($id)
            ? $this->resultPageFactory->create()
            : $this->resultForwardFactory->create()->forward('noroute');
    }
}
