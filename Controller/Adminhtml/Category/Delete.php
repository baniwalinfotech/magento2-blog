<?php

namespace Baniwal\Blog\Controller\Adminhtml\Category;

use Baniwal\Blog\Controller\Adminhtml\Category;

class Delete extends Category
{
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($id = $this->getRequest()->getParam('id')) {
            try {
                $this->categoryFactory->create()
                    ->load($id)
                    ->delete();

                $this->messageManager->addSuccess(__('The Blog Category has been deleted.'));

                $resultRedirect->setPath('baniwal_blog/*/');

                return $resultRedirect;
            } catch (\Exception $e) {
                // display error message
                $this->messageManager->addError($e->getMessage());
                // go back to edit form
                $resultRedirect->setPath('baniwal_blog/*/edit', ['id' => $id]);

                return $resultRedirect;
            }
        }

        // display error message
        $this->messageManager->addError(__('Blog Category to delete was not found.'));
        // go to grid
        $resultRedirect->setPath('baniwal_blog/*/');

        return $resultRedirect;
    }
}
