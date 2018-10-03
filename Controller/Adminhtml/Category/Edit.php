<?php

namespace Baniwal\Blog\Controller\Adminhtml\Category;

use Magento\Backend\App\Action\Context;
use Magento\Catalog\Model\Category as CategoryModel;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\DataObject;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Baniwal\Blog\Controller\Adminhtml\Category;
use Baniwal\Blog\Model\CategoryFactory;

class Edit extends Category
{
    public $resultPageFactory;

    public $resultJsonFactory;

    public $dataObject;

    public function __construct(
        Context $context,
        Registry $registry,
        CategoryFactory $categoryFactory,
        DataObject $dataObject,
        PageFactory $resultPageFactory,
        JsonFactory $resultJsonFactory
    )
    {
        $this->dataObject = $dataObject;
        $this->resultPageFactory = $resultPageFactory;
        $this->resultJsonFactory = $resultJsonFactory;

        parent::__construct($context, $registry, $categoryFactory);
    }

    public function execute()
    {
        $categoryId = (int)$this->getRequest()->getParam('id');

        $category = $this->initCategory();
        if (!$category) {
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('*');

            return $resultRedirect;
        }

        $data = $this->_getSession()->getData('baniwal_blog_category_data', true);
        if (isset($data['category'])) {
            $category->addData($data['category']);
        }

        $this->coreRegistry->register('category', $category);

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();

        /** Build response for ajax request */
        if ($this->getRequest()->getQuery('isAjax')) {
            // prepare breadcrumbs of selected Blog category, if any
            $breadcrumbsPath = $category->getPath();
            if (empty($breadcrumbsPath)) {
                // but if no Blog category, and it is deleted - prepare breadcrumbs from path, saved in session
                $breadcrumbsPath = $this->_objectManager->get('Magento\Backend\Model\Auth\Session')
                    ->getDeletedPath(true);
                if (!empty($breadcrumbsPath)) {
                    $breadcrumbsPath = explode('/', $breadcrumbsPath);
                    // no need to get parent breadcrumbs if deleting Blog category level 1
                    if (count($breadcrumbsPath) <= 1) {
                        $breadcrumbsPath = '';
                    } else {
                        array_pop($breadcrumbsPath);
                        $breadcrumbsPath = implode('/', $breadcrumbsPath);
                    }
                }
            }

            $layout = $resultPage->getLayout();
            $content = $layout->getBlock('baniwal.blog.category.edit')->getFormHtml()
                . $layout->getBlock('baniwal.blog.category.tree')
                    ->getBreadcrumbsJavascript($breadcrumbsPath, 'editingCategoryBreadcrumbs');
            $eventResponse = $this->dataObject->addData([
                'content' => $content,
                'messages' => $layout->getMessagesBlock()->getGroupedHtml(),
                'toolbar' => $layout->getBlock('page.actions.toolbar')->toHtml()
            ]);

            $this->_eventManager->dispatch(
                'baniwal_blog_category_prepare_ajax_response',
                ['response' => $eventResponse, 'controller' => $this]
            );

            /** @var \Magento\Framework\Controller\Result\Json $resultJson */
            $resultJson = $this->resultJsonFactory->create();
            $resultJson->setHeader('Content-type', 'application/json', true);
            $resultJson->setData($eventResponse->getData());

            return $resultJson;
        }

        $resultPage->setActiveMenu('Baniwal_Blog::category');
        $resultPage->getConfig()->getTitle()->prepend(__('Categories'));

        if ($categoryId) {
            $title = __('%1 (ID: %2)', $category->getName(), $categoryId);
        } else {
            $parentId = (int)$this->getRequest()->getParam('parent');
            if ($parentId && $parentId != CategoryModel::TREE_ROOT_ID) {
                $title = __('New Child Category');
            } else {
                $title = __('New Root Category');
            }
        }
        $resultPage->getConfig()->getTitle()->prepend($title);

        $resultPage->addBreadcrumb(__('Manage Categories'), __('Manage Categories'));

        return $resultPage;
    }
}
