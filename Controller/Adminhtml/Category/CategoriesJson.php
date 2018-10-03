<?php

namespace Baniwal\Blog\Controller\Adminhtml\Category;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Registry;
use Magento\Framework\View\LayoutFactory;
use Baniwal\Blog\Controller\Adminhtml\Category;
use Baniwal\Blog\Model\CategoryFactory;

class CategoriesJson extends Category
{
    public $resultJsonFactory;

    public $layoutFactory;

    public function __construct(
        Context $context,
        Registry $coreRegistry,
        CategoryFactory $categoryFactory,
        JsonFactory $resultJsonFactory,
        LayoutFactory $layoutFactory
    )
    {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->layoutFactory = $layoutFactory;

        parent::__construct($context, $coreRegistry, $categoryFactory);
    }

    public function execute()
    {
        $this->_objectManager->get('Magento\Backend\Model\Auth\Session')->setIsTreeWasExpanded(
            (boolean)$this->getRequest()->getParam('expand_all')
        );

        $resultJson = $this->resultJsonFactory->create();
        if ($categoryId = (int)$this->getRequest()->getPost('id')) {
            $this->getRequest()->setParam('id', $categoryId);

            $category = $this->initCategory(true);
            if (!$category) {
                /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();

                return $resultRedirect->setPath('baniwal_blog/*/', ['_current' => true]);
            }

            $treeJson = $this->layoutFactory->create()
                ->createBlock('Baniwal\Blog\Block\Adminhtml\Category\Tree')
                ->getTreeJson($category);

            /** @var \Magento\Framework\Controller\Result\Json $resultJson */
            return $resultJson->setJsonData($treeJson);
        }

        return $resultJson->setJsonData('[]');
    }
}
