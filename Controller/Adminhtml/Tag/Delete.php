<?php

namespace Baniwal\Blog\Controller\Adminhtml\Tag;

use Baniwal\Blog\Controller\Adminhtml\Tag;

class Delete extends Tag
{
    /**
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($id = $this->getRequest()->getParam('id')) {
            try {
                $this->tagFactory->create()
                    ->load($id)
                    ->delete();
                $this->messageManager->addSuccess(__('The Tag has been deleted.'));
            } catch (\Exception $e) {
                // display error message
                $this->messageManager->addError($e->getMessage());
                // go back to edit form
                $resultRedirect->setPath('baniwal_blog/*/edit', ['id' => $id]);

                return $resultRedirect;
            }
        } else {
            // display error message
            $this->messageManager->addError(__('Tag to delete was not found.'));
        }

        // go to grid
        $resultRedirect->setPath('baniwal_blog/*/');

        return $resultRedirect;
    }
}
