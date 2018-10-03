<?php

namespace Baniwal\Blog\Controller\Adminhtml\Comment;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Ui\Component\MassAction\Filter;
use Baniwal\Blog\Model\ResourceModel\Comment\CollectionFactory;

class MassDelete extends Action
{
    public $filter;

    public $collectionFactory;

    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory
    )
    {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());

        try {
            $count = 0;
            foreach ($collection as $item) {
                $item->delete();
                $count++;
            }
            $this->messageManager->addSuccessMessage(__('Total of %1 comments has been deleted.', $count));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Something wrong when delete Comments.'));
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        return $resultRedirect->setPath('*/*/');
    }
}
