<?php

namespace Baniwal\Blog\Block\Adminhtml;

use Magento\Backend\Block\Widget\Grid\Container;

class Category extends Container
{
    protected function _construct()
    {
        $this->_controller = 'adminhtml_category';
        $this->_blockGroup = 'Baniwal_Blog';
        $this->_headerText = __('Categories');
        $this->_addButtonLabel = __('Create New Blog Category');

        parent::_construct();
    }
}
