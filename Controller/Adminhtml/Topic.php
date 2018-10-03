<?php

namespace Baniwal\Blog\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Baniwal\Blog\Model\TopicFactory;

abstract class Topic extends Action
{
    /** Authorization level of a basic admin session */
    const ADMIN_RESOURCE = 'Baniwal_Blog::topic';

    public $topicFactory;

    public $coreRegistry;

    public function __construct(
        Context $context,
        Registry $coreRegistry,
        TopicFactory $topicFactory
    )
    {
        $this->topicFactory = $topicFactory;
        $this->coreRegistry = $coreRegistry;

        parent::__construct($context);
    }

    protected function initTopic($register = false)
    {
        $topicId = (int)$this->getRequest()->getParam('id');

        $topic = $this->topicFactory->create();
        if ($topicId) {
            $topic->load($topicId);
            if (!$topic->getId()) {
                $this->messageManager->addErrorMessage(__('This topic no longer exists.'));

                return false;
            }
        }

        if ($register) {
            $this->coreRegistry->register('baniwal_blog_topic', $topic);
        }

        return $topic;
    }
}
