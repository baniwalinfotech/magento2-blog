<?php

namespace Baniwal\Blog\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Baniwal\Blog\Model\TagFactory;


abstract class Tag extends Action
{
    /** Authorization level of a basic admin session */
    const ADMIN_RESOURCE = 'Baniwal_Blog::tag';

    public $tagFactory;

    public $coreRegistry;

    public function __construct(
        Context $context,
        Registry $coreRegistry,
        TagFactory $tagFactory
    )
    {
        $this->tagFactory = $tagFactory;
        $this->coreRegistry = $coreRegistry;

        parent::__construct($context);
    }

    protected function initTag($register = false)
    {
        $tagId = (int)$this->getRequest()->getParam('id');

        $tag = $this->tagFactory->create();
        if ($tagId) {
            $tag->load($tagId);
            if (!$tag->getId()) {
                $this->messageManager->addErrorMessage(__('This tag no longer exists.'));

                return false;
            }
        }

        if ($register) {
            $this->coreRegistry->register('baniwal_blog_tag', $tag);
        }

        return $tag;
    }
}
