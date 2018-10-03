<?php

namespace Baniwal\Blog\Block;

class Sitemap extends Frontend
{
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        if ($breadcrumbs = $this->getLayout()->getBlock('breadcrumbs')) {
            $breadcrumbs->addCrumb('sitemap', [
                    'label' => __('Site Map'),
                    'title' => __('Site Map')
                ]
            );
        }
    }

    public function getBlogTitle($meta = false)
    {
        $blogTitle = parent::getBlogTitle($meta);

        if ($meta) {
            $blogTitle[] = __('Site Map');
        } else {
            $blogTitle = __('Site Map');
        }

        return $blogTitle;
    }
}
