<?php

namespace Baniwal\Blog\Block\Html;

use Magento\Framework\View\Element\Html\Link;
use Magento\Framework\View\Element\Template\Context;
use Baniwal\Blog\Helper\Data;

class Footer extends Link
{
    public $helper;

    protected $_template = 'Baniwal_Blog::html\footer.phtml';

    public function __construct(
        Context $context,
        Data $helper,
        array $data = []
    )
    {
        $this->helper = $helper;

        parent::__construct($context, $data);
    }

    public function getHref()
    {
        return $this->helper->getBlogUrl('');
    }

    public function getLabel()
    {
        if ($this->helper->getBlogConfig('general/name') == "") {
            return __("Blog");
        }

        return $this->helper->getBlogConfig('general/name');
    }

    public function getHtmlSiteMapUrl()
    {
        return $this->helper->getBlogUrl('sitemap');
    }
}
