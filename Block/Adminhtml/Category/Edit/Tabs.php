<?php

namespace Baniwal\Blog\Block\Adminhtml\Category\Edit;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Registry;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    protected $_template = 'Magento_Backend::widget/tabshoriz.phtml';

    public $coreRegistry;

    public function __construct(
        Context $context,
        Registry $coreRegistry,
        EncoderInterface $jsonEncoder,
        Session $authSession,
        array $data = []
    )
    {
        $this->coreRegistry = $coreRegistry;

        parent::__construct($context, $jsonEncoder, $authSession, $data);
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setId('category_info_tabs');
        $this->setDestElementId('category_tab_content');
        $this->setTitle(__('Category Data'));
    }

    public function getCategory()
    {
        return $this->coreRegistry->registry('category');
    }

    protected function _prepareLayout()
    {
        $this->addTab('category', [
                'label' => __('Category information'),
                'content' => $this->getLayout()
                    ->createBlock('Baniwal\Blog\Block\Adminhtml\Category\Edit\Tab\Category', 'baniwal_blog_category_edit_tab_category')
                    ->toHtml()
            ]
        );
        $this->addTab('post', [
                'label' => __('Posts'),
                'content' => $this->getLayout()
                    ->createBlock('Baniwal\Blog\Block\Adminhtml\Category\Edit\Tab\Post', 'baniwal_blog_category_edit_tab_post')
                    ->toHtml()
            ]
        );

        // dispatch event add custom tabs
        $this->_eventManager->dispatch('adminhtml_baniwal_blog_category_tabs', ['tabs' => $this]);

        return parent::_prepareLayout();
    }
}
