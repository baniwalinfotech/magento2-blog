<?php

namespace Baniwal\Blog\Controller\Adminhtml\Topic;

use Baniwal\Blog\Controller\Adminhtml\Topic;

class Delete extends Topic
{
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($id = $this->getRequest()->getParam('id')) {
            try {
                $this->topicFactory->create()
                    ->load($id)
                    ->delete();

                $this->messageManager->addSuccess(__('The Topic has been deleted.'));
            } catch (\Exception $e) {
                // display error message
                $this->messageManager->addError($e->getMessage());
                // go back to edit form
                $resultRedirect->setPath('baniwal_blog/*/edit', ['id' => $id]);

                return $resultRedirect;
            }
        } else {
            // display error message
            $this->messageManager->addError(__('Topic to delete was not found.'));
        }

        // go to grid
        $resultRedirect->setPath('baniwal_blog/*/');

        return $resultRedirect;
    }
}
