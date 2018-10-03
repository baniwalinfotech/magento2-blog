<?php

namespace Baniwal\Blog\Controller\Adminhtml\Tag;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Helper\Js;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\MessageInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\LayoutFactory;
use Baniwal\Blog\Controller\Adminhtml\Tag;
use Baniwal\Blog\Model\TagFactory;
use Psr\Log\LoggerInterface;

class Save extends Tag
{
    /**
     * @var \Magento\Backend\Helper\Js
     */
    public $jsHelper;

    /**
     * Layout Factory
     *
     * @var \Magento\Framework\View\LayoutFactory
     */
    public $layoutFactory;

    /**
     * Result Json Factory
     *
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    public $resultJsonFactory;

    /**
     * Save constructor.
     * @param Context $context
     * @param Registry $registry
     * @param Js $jsHelper
     * @param LayoutFactory $layoutFactory
     * @param JsonFactory $resultJsonFactory
     * @param TagFactory $tagFactory
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Js $jsHelper,
        LayoutFactory $layoutFactory,
        JsonFactory $resultJsonFactory,
        TagFactory $tagFactory
    )
    {
        $this->jsHelper = $jsHelper;
        $this->layoutFactory = $layoutFactory;
        $this->resultJsonFactory = $resultJsonFactory;

        parent::__construct($context, $registry, $tagFactory);
    }

    /**
     * @return $this|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        if ($this->getRequest()->getPost('return_session_messages_only')) {
            $tag = $this->initTag();
            $tagPostData = $this->getRequest()->getPostValue();
            $tagPostData['store_ids'] = 0;
            $tagPostData['enabled'] = 1;

            $tag->addData($tagPostData);

            try {
                $tag->save();
                $this->messageManager->addSuccess(__('You saved the tag.'));
            } catch (AlreadyExistsException $e) {
                $this->messageManager->addError($e->getMessage());
                $this->_objectManager->get(LoggerInterface::class)->critical($e);
            } catch (LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
                $this->_objectManager->get(LoggerInterface::class)->critical($e);
            } catch (\Exception $e) {
                $this->messageManager->addError(__('Something went wrong while saving the tag.'));
                $this->_objectManager->get(LoggerInterface::class)->critical($e);
            }

            $hasError = (bool)$this->messageManager->getMessages()->getCountByType(
                MessageInterface::TYPE_ERROR
            );

            $tag->load($tag->getId());
            $tag->addData([
                'level' => 1,
                'entity_id' => $tag->getId(),
                'is_active' => $tag->getEnabled(),
                'parent' => 0
            ]);

            // to obtain truncated category name
            /** @var $block \Magento\Framework\View\Element\Messages */
            $block = $this->layoutFactory->create()->getMessagesBlock();
            $block->setMessages($this->messageManager->getMessages(true));

            /** @var \Magento\Framework\Controller\Result\Json $resultJson */
            $resultJson = $this->resultJsonFactory->create();

            return $resultJson->setData([
                    'messages' => $block->getGroupedHtml(),
                    'error' => $hasError,
                    'category' => $tag->toArray(),
                ]
            );
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data = $this->getRequest()->getPost('tag')) {
            /** @var \Baniwal\Blog\Model\Tag $tag */
            $tag = $this->initTag();

            $tag->addData($data);
            if ($posts = $this->getRequest()->getPost('posts', false)) {
                $tag->setPostsData($this->jsHelper->decodeGridSerializedInput($posts));
            }

            $this->_eventManager->dispatch('baniwal_blog_tag_prepare_save', ['tag' => $tag, 'request' => $this->getRequest()]);

            try {
                $tag->save();

                $this->messageManager->addSuccess(__('The Tag has been saved.'));
                $this->_session->setData('baniwal_blog_tag_data', false);

                if ($this->getRequest()->getParam('back')) {
                    $resultRedirect->setPath('baniwal_blog/*/edit', ['id' => $tag->getId(), '_current' => true]);
                } else {
                    $resultRedirect->setPath('baniwal_blog/*/');
                }

                return $resultRedirect;
            } catch (LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the Tag.'));
            }
            $this->_getSession()->setData('baniwal_blog_tag_data', $data);

            $resultRedirect->setPath('baniwal_blog/*/edit', ['id' => $tag->getId(), '_current' => true]);

            return $resultRedirect;
        }

        $resultRedirect->setPath('baniwal_blog/*/');

        return $resultRedirect;
    }
}
