<?php

namespace Baniwal\Blog\Controller\Adminhtml\Comment;

use Magento\Framework\Exception\LocalizedException;
use Baniwal\Blog\Controller\Adminhtml\Comment;
use Baniwal\Blog\Model\CommentFactory;

class Save extends Comment
{
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($data = $this->getRequest()->getPost('comment')) {

            $comment = $this->initComment();

            $this->prepareData($comment, $data);

            $this->_eventManager->dispatch('baniwal_blog_comment_prepare_save', ['comment' => $comment, 'request' => $this->getRequest()]);

            try {
                $comment->save();

                $this->messageManager->addSuccess(__('The comment has been saved.'));
                $this->_getSession()->setData('baniwal_blog_comment_data', false);

                if ($this->getRequest()->getParam('back')) {
                    $resultRedirect->setPath('baniwal_blog/*/edit', ['id' => $comment->getId(), '_current' => true]);
                } else {
                    $resultRedirect->setPath('baniwal_blog/*/');
                }

                return $resultRedirect;
            } catch (LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the Comment.'));
            }

            $this->_getSession()->setData('baniwal_blog_comment_data', $data);

            $resultRedirect->setPath('baniwal_blog/*/edit', ['id' => $comment->getId(), '_current' => true]);

            return $resultRedirect;
        }

        $resultRedirect->setPath('baniwal_blog/*/');

        return $resultRedirect;
    }

    protected function prepareData($comment, $data = [])
    {
        $comment->addData($data);

        return $this;
    }
}
