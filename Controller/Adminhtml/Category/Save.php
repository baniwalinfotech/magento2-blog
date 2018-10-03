<?php
namespace Baniwal\Blog\Controller\Adminhtml\Category;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Helper\Js;
use Magento\Catalog\Model\Category as CategoryModel;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\MessageInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\LayoutFactory;
use Baniwal\Blog\Controller\Adminhtml\Category;
use Baniwal\Blog\Model\CategoryFactory;
use Psr\Log\LoggerInterface;

class Save extends Category
{
    public $resultRawFactory;

    public $resultJsonFactory;

    public $layoutFactory;

    public $jsHelper;

    public function __construct(
        Context $context,
        Registry $coreRegistry,
        CategoryFactory $categoryFactory,
        RawFactory $resultRawFactory,
        JsonFactory $resultJsonFactory,
        LayoutFactory $layoutFactory,
        Js $jsHelper
    )
    {
        $this->resultRawFactory = $resultRawFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->layoutFactory = $layoutFactory;
        $this->jsHelper = $jsHelper;

        parent::__construct($context, $coreRegistry, $categoryFactory);
    }

    public function execute()
    {
        if ($this->getRequest()->getPost('return_session_messages_only')) {
            $category = $this->initCategory();
            $categoryPostData = $this->getRequest()->getPostValue();
            $categoryPostData['store_ids'] = 0;
            $categoryPostData['enabled'] = 1;

            $category->addData($categoryPostData);

            $parentId = $this->getRequest()->getParam('parent');
            if (!$parentId) {
                $parentId = CategoryModel::TREE_ROOT_ID;
            }
            $parentCategory = $this->categoryFactory->create()->load($parentId);
            $category->setPath($parentCategory->getPath());
            $category->setParentId($parentId);

            try {
                $category->save();
                $this->messageManager->addSuccess(__('You saved the category.'));
            } catch (AlreadyExistsException $e) {
                $this->messageManager->addError($e->getMessage());
                $this->_objectManager->get(LoggerInterface::class)->critical($e);
            } catch (LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
                $this->_objectManager->get(LoggerInterface::class)->critical($e);
            } catch (\Exception $e) {
                $this->messageManager->addError(__('Something went wrong while saving the category.'));
                $this->_objectManager->get(LoggerInterface::class)->critical($e);
            }

            $hasError = (bool)$this->messageManager->getMessages()->getCountByType(
                MessageInterface::TYPE_ERROR
            );

            $category->load($category->getId());
            $category->addData([
                'entity_id' => $category->getId(),
                'is_active' => $category->getEnabled(),
                'parent' => $category->getParentId()
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
                    'category' => $category->toArray(),
                ]
            );

        }

        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data = $this->getRequest()->getPost('category')) {
            $category = $this->initCategory();
            if (!$category) {
                $resultRedirect->setPath('baniwal_blog/*/', ['_current' => true]);

                return $resultRedirect;
            }

            $category->addData($data);
            if ($posts = $this->getRequest()->getPost('selected_products')) {
                $posts = json_decode($posts, true);
                $category->setPostsData($posts);
            }

            if (!$category->getId()) {
                $parentId = $this->getRequest()->getParam('parent');
                if (!$parentId) {
                    $parentId = CategoryModel::TREE_ROOT_ID;
                }
                $parentCategory = $this->categoryFactory->create()->load($parentId);
                $category->setPath($parentCategory->getPath());
                $category->setParentId($parentId);
            }

            $this->_eventManager->dispatch(
                'baniwal_blog_category_prepare_save',
                ['category' => $category, 'request' => $this->getRequest()]
            );

            try {
                $category->save();
                $this->messageManager->addSuccess(__('You saved the Blog Category.'));
                $this->_getSession()->setData('baniwal_blog_category_data', false);
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                $this->_getSession()->setData('baniwal_blog_category_data', $data);
            }

            $resultRedirect->setPath('baniwal_blog/*/edit', ['_current' => true, 'id' => $category->getId()]);

            return $resultRedirect;
        }

        $resultRedirect->setPath('baniwal_blog/*/edit', ['_current' => true]);

        return $resultRedirect;
    }
}
