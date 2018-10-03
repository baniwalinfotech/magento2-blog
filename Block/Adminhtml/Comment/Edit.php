<?php

namespace Baniwal\Blog\Block\Adminhtml\Comment;

use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Block\Widget\Form\Container;
use Magento\Framework\Registry;

class Edit extends Container
{
    public $coreRegistry;

    public function __construct(
        Registry $coreRegistry,
        Context $context,
        array $data = []
    )
    {
        $this->coreRegistry = $coreRegistry;
        parent::__construct($context, $data);
    }

    protected function _construct()
    {
        $this->_blockGroup = 'Baniwal_Blog';
        $this->_controller = 'adminhtml_comment';

        parent::_construct();

        $this->buttonList->add(
            'save-and-continue',
            [
                'label' => __('Save and Continue Edit'),
                'class' => 'save',
                'data_attribute' => [
                    'mage-init' => [
                        'button' => [
                            'event' => 'saveAndContinueEdit',
                            'target' => '#edit_form'
                        ]
                    ]
                ]
            ],
            -100
        );
        $this->buttonList->update('save', 'label', 'Save Comment');
        $this->buttonList->remove('reset');

    }

    public function getHeaderText()
    {
        $comment = $this->coreRegistry->registry('baniwal_blog_comment');
        if ($comment->getId()) {
            return __("Edit Comment");
        }

        return __('New Comment');
    }

    public function getFormActionUrl()
    {
        $comment = $this->coreRegistry->registry('baniwal_blog_comment');
        if ($id = $comment->getId()) {
            return $this->getUrl('*/*/save', ['id' => $id]);
        }

        return parent::getFormActionUrl();
    }
}
