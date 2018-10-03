<?php

namespace Baniwal\Blog\Block\Adminhtml\Category;

use Magento\Backend\Block\Widget\Form\Container;

class Edit extends Container
{
    /**
     * prepare the form
     */
    protected function _construct()
    {
        $this->_objectId = 'id';
        $this->_blockGroup = 'Baniwal_Blog';
        $this->_controller = 'adminhtml_category';

        parent::_construct();

        $this->buttonList->remove('back');
        $this->buttonList->remove('reset');
        $this->buttonList->remove('save');
    }
}
