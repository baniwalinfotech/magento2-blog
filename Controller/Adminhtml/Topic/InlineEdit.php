<?php

namespace Baniwal\Blog\Controller\Adminhtml\Topic;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\LocalizedException;
use Baniwal\Blog\Model\TopicFactory;

class InlineEdit extends Action
{
    public $jsonFactory;

    public $topicFactory;

    public function __construct(
        Context $context,
        JsonFactory $jsonFactory,
        TopicFactory $topicFactory
    )
    {
        $this->jsonFactory = $jsonFactory;
        $this->topicFactory = $topicFactory;

        parent::__construct($context);
    }

    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->jsonFactory->create();
        $error = false;
        $messages = [];
        $postItems = $this->getRequest()->getParam('items', []);

        if (!($this->getRequest()->getParam('isAjax') && !empty($postItems))) {
            return $resultJson->setData([
                'messages' => [__('Please correct the data sent.')],
                'error' => true,
            ]);
        }

        $key = array_keys($postItems);
        $topicId = !empty($key) ? (int)$key[0] : '';

        $topic = $this->topicFactory->create()->load($topicId);
        try {
            $topic->addData($postItems[$topicId])
                ->save();
        } catch (LocalizedException $e) {
            $messages[] = $this->getErrorWithTopicId($topic, $e->getMessage());
            $error = true;
        } catch (\RuntimeException $e) {
            $messages[] = $this->getErrorWithTopicId($topic, $e->getMessage());
            $error = true;
        } catch (\Exception $e) {
            $messages[] = $this->getErrorWithTopicId(
                $topic,
                __('Something went wrong while saving the Topic.')
            );
            $error = true;
        }

        return $resultJson->setData([
            'messages' => $messages,
            'error' => $error
        ]);
    }

    public function getErrorWithTopicId(\Baniwal\Blog\Model\Topic $topic, $errorText)
    {
        return '[Topic ID: ' . $topic->getId() . '] ' . $errorText;
    }
}
