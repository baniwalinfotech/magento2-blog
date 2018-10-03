<?php

namespace Baniwal\Blog\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Baniwal\Blog\Model\PostFactory;

abstract class Post extends Action
{
    /** Authorization level of a basic admin session */
    const ADMIN_RESOURCE = 'Baniwal_Blog::post';

    public $postFactory;

    public $coreRegistry;

    public function __construct(
        PostFactory $postFactory,
        Registry $coreRegistry,
        Context $context
    )
    {
        $this->postFactory = $postFactory;
        $this->coreRegistry = $coreRegistry;

        parent::__construct($context);
    }

    protected function initPost($register = false)
    {
        $postId = (int)$this->getRequest()->getParam('id');

        $post = $this->postFactory->create();
        if ($postId) {
            $post->load($postId);
            if (!$post->getId()) {
                $this->messageManager->addErrorMessage(__('This post no longer exists.'));

                return false;
            }
        }

        if (!$post->getAuthorId()) {
            $post->setAuthorId($this->_auth->getUser()->getId());
        }

        if ($register) {
            $this->coreRegistry->register('baniwal_blog_post', $post);
        }

        return $post;
    }
}
