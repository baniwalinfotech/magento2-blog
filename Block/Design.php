<?php

namespace Baniwal\Blog\Block;

use Magento\Framework\View\Design\Theme\ThemeProviderInterface;
use Magento\Framework\View\DesignInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Baniwal\Blog\Helper\Data as HelperData;

class Design extends Template
{
    public $helperData;

    protected $_themeProvider;

    public function __construct(
        Context $context,
        HelperData $helperData,
        ThemeProviderInterface $_themeProvider,
        array $data = []
    )
    {
        $this->helperData = $helperData;
        $this->_themeProvider = $_themeProvider;

        parent::__construct($context, $data);
    }

    public function getHelper()
    {
        return $this->helperData;
    }

    public function isSidebarRight()
    {
        return $this->helperData->getBlogConfig('sidebar/sidebar_left_right');
    }

    public function getCurrentTheme()
    {
        $themeId = $this->helperData->getConfigValue(DesignInterface::XML_PATH_THEME_ID);

        $theme = $this->_themeProvider->getThemeById($themeId);

        return $theme->getCode();
    }
}
