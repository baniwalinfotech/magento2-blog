<?php

namespace Baniwal\Blog\Block\Adminhtml;

use Magento\Backend\Block\Widget\Grid\Container;

class Comment extends Container
{
    /**
     * constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_comment';
        $this->_blockGroup = 'Baniwal_Blog';
        $this->_addButtonLabel = __('New Comment');

        parent::_construct();
    }
}
