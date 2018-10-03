<?php
namespace Baniwal\Blog\Plugin;

class InfortisTopmenu
{
    public function afterRenderCategoriesMenuHtml(\Infortis\UltraMegamenu\Block\Navigation $topmenu, $html)
    {
        $html .= $topmenu->getLayout()
            ->createBlock('Baniwal\Blog\Block\Frontend')
            ->setTemplate('Baniwal_Blog::position/topmenuinfortis.phtml')
            ->toHtml();

        return $html;
    }
}
