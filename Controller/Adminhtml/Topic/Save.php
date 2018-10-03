<?php

namespace Baniwal\Blog\Controller\Adminhtml\Topic;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Helper\Js;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\MessageInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\LayoutFactory;
use Baniwal\Blog\Controller\Adminhtml\Topic;
use Baniwal\Blog\Model\TopicFactory;
use Psr\Log\LoggerInterface;

class Save extends Topic
{
    public $jsHelper;

    public $layoutFactory;

    public $resultJsonFactory;

    public function __construct(
        Context $context,
        Registry $registry,
        Js $jsHelper,
        LayoutFactory $layoutFactory,
        JsonFactory $resultJsonFactory,
        TopicFactory $topicFactory
    )
    {
        $this->jsHelper = $jsHelper;
        $this->layoutFactory = $layoutFactory;
        $this->resultJsonFactory = $resultJsonFactory;

        parent::__construct($context, $registry, $topicFactory);
    }

    public function execute()
    {
        if ($this->getRequest()->getPost('return_session_messages_only')) {
            $topic = $this->initTopic();
            $topicPostData = $this->getRequest()->getPostValue();
            $topicPostData['store_ids'] = 0;
            $topicPostData['enabled'] = 1;

            $topic->addData($topicPostData);

            try {
                $topic->save();
                $this->messageManager->addSuccess(__('You saved the topic.'));
            } catch (AlreadyExistsException $e) {
                $this->messageManager->addError($e->getMessage());
                $this->_objectManager->get(LoggerInterface::class)->critical($e);
            } catch (LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
                $this->_objectManager->get(LoggerInterface::class)->critical($e);
            } catch (\Exception $e) {
                $this->messageManager->addError(__('Something went wrong while saving the topic.'));
                $this->_objectManager->get(LoggerInterface::class)->critical($e);
            }

            $hasError = (bool)$this->messageManager->getMessages()->getCountByType(
                MessageInterface::TYPE_ERROR
            );

            $topic->load($topic->getId());
            $topic->addData([
                'level' => 1,
                'entity_id' => $topic->getId(),
                'is_active' => $topic->getEnabled(),
                'parent' => 0
            ]);

            // to obtain truncated category name
            /** @var $block \Magento\Framework\View\Element\Messages */
            $block = $this->layoutFactory->create()->getMessagesBlock();
            $block->setMessages($this->messageManager->getMessages(true));

            /** @var \Magento\Framework\Controller\Result\Json $resultJson */
            $resultJson = $this->resultJsonFactory->create();

            return $resultJson->setData(
                [
                    'messages' => $block->getGroupedHtml(),
                    'error' => $hasError,
                    'category' => $topic->toArray(),
                ]
            );
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data = $this->getRequest()->getPost('topic')) {
            /** @var \Baniwal\Blog\Model\Topic $topic */
            $topic = $this->initTopic();
            $topic->setData($data);

            if ($posts = $this->getRequest()->getPost('posts', false)) {
                $topic->setPostsData($this->jsHelper->decodeGridSerializedInput($posts));
            }

            try {
                $topic->save();

                $this->messageManager->addSuccess(__('The Topic has been saved.'));
                $this->_getSession()->setData('baniwal_blog_topic_data', false);

                if ($this->getRequest()->getParam('back')) {
                    $resultRedirect->setPath('baniwal_blog/*/edit', ['id' => $topic->getId(), '_current' => true]);
                } else {
                    $resultRedirect->setPath('baniwal_blog/*/');
                }

                return $resultRedirect;
            } catch (LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the Topic.'));
            }

            $this->_getSession()->setData('baniwal_blog_topic_data', $data);

            $resultRedirect->setPath('baniwal_blog/*/edit', ['id' => $topic->getId(), '_current' => true]);

            return $resultRedirect;
        }

        $resultRedirect->setPath('baniwal_blog/*/');

        return $resultRedirect;
    }
}
