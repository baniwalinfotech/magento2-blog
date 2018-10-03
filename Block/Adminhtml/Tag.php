<?php

namespace Baniwal\Blog\Block\Adminhtml;

use Magento\Backend\Block\Widget\Grid\Container;

class Tag extends Container
{
    /**
     * constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_tag';
        $this->_blockGroup = 'Baniwal_Blog';
        $this->_headerText = __('Tags');
        $this->_addButtonLabel = __('Create New Tag');

        parent::_construct();
    }
}
