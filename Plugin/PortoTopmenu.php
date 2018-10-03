<?php

namespace Baniwal\Blog\Plugin;

class PortoTopmenu
{
    public function afterGetMegamenuHtml(\Smartwave\Megamenu\Block\Topmenu $topmenu, $html)
    {
        $html .= $topmenu->getLayout()
            ->createBlock('Baniwal\Blog\Block\Frontend')
            ->setTemplate('Baniwal_Blog::position/topmenuporto.phtml')
            ->toHtml();

        return $html;
    }
}
