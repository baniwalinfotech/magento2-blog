<?php

namespace Baniwal\Blog\Controller\Adminhtml\Tag;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Ui\Component\MassAction\Filter;
use Baniwal\Blog\Model\ResourceModel\Tag\CollectionFactory;

class MassStatus extends Action
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
        $status = (int)$this->getRequest()->getParam('status');

        $tagUpdated = 0;
        foreach ($collection as $tag) {
            try {
                $tag->setEnabled($status)
                    ->save();

                $tagUpdated++;
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->_getSession()->addException($e, __('Something went wrong while updating status for %1.', $tag->getName()));
            }
        }

        if ($tagUpdated) {
            $this->messageManager->addSuccessMessage(__('A total of %1 record(s) have been updated.', $tagUpdated));
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        return $resultRedirect->setPath('*/*/');
    }
}
