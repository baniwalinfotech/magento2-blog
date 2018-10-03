<?php

namespace Baniwal\Blog\Controller\Adminhtml\Topic;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\LayoutFactory;
use Baniwal\Blog\Controller\Adminhtml\Topic;
use Baniwal\Blog\Model\TopicFactory;

/**
 * Class Posts
 * @package Baniwal\Blog\Controller\Adminhtml\Topic
 */
class Posts extends Topic
{
    public $resultLayoutFactory;

    public function __construct(
        Context $context,
        Registry $registry,
        LayoutFactory $resultLayoutFactory,
        TopicFactory $postFactory
    )
    {
        $this->resultLayoutFactory = $resultLayoutFactory;

        parent::__construct($context, $registry, $postFactory);
    }

    public function execute()
    {
        $this->initTopic(true);

        return $this->resultLayoutFactory->create();
    }
}
