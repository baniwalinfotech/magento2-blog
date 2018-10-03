<?php

namespace Baniwal\Blog\Block\Adminhtml\Renderer;

class Image extends \Magento\Framework\Data\Form\Element\Image
{
    protected function _getUrl()
    {
        $url = parent::_getUrl();

        if ($this->getPath()) {
            $url = $this->getPath() . '/' . trim($url, '/');
        }

        return $url;
    }
}
