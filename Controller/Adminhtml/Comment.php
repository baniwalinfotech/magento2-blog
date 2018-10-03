<?php

namespace Baniwal\Blog\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Baniwal\Blog\Model\CommentFactory;

abstract class Comment extends Action
{
    /** Authorization level of a basic admin session */
    const ADMIN_RESOURCE = 'Baniwal_Blog::comment';

    public $commentFactory;

    public $coreRegistry;

    public function __construct(
        CommentFactory $commentFactory,
        Registry $coreRegistry,
        Context $context
    )
    {
        $this->commentFactory = $commentFactory;
        $this->coreRegistry = $coreRegistry;

        parent::__construct($context);
    }

    protected function initComment($register = false)
    {
        $cmtId = $this->getRequest()->getParam("id");

        /** @var \Baniwal\Blog\Model\Post $post */
        $comment = $this->commentFactory->create();

        if ($cmtId) {
            $comment->load($cmtId);
            if (!$comment->getId()) {
                $this->messageManager->addErrorMessage(__('This comment no longer exists.'));

                return false;
            }
        }

        if ($register) {
            $this->coreRegistry->register('baniwal_blog_comment', $comment);
        }

        return $comment;
    }
}
