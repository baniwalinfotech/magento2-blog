<?php

namespace Baniwal\Blog\Block\Adminhtml\Comment\Edit;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        $this->setId('comment_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Comment Information'));
    }
}
