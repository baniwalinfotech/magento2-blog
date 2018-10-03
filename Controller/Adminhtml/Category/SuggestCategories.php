<?php

namespace Baniwal\Blog\Controller\Adminhtml\Category;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Registry;
use Magento\Framework\View\LayoutFactory;
use Baniwal\Blog\Controller\Adminhtml\Category;
use Baniwal\Blog\Model\CategoryFactory;

class SuggestCategories extends Category
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
        $treeBlock = $this->layoutFactory->create()->createBlock('Baniwal\Blog\Block\Adminhtml\Category\Tree');
        $data = $treeBlock->getSuggestedCategoriesJson($this->getRequest()->getParam('label_part'));

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        $resultJson->setJsonData($data);

        return $resultJson;
    }
}
