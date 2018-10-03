<?php

namespace Baniwal\Blog\Block\MonthlyArchive;

use Baniwal\Blog\Helper\Data;

class Listpost extends \Baniwal\Blog\Block\Listpost
{
    protected function getCollection()
    {
        return $this->helperData->getPostCollection(Data::TYPE_MONTHLY, $this->getMonthKey());
    }

    protected function getMonthKey()
    {
        return $this->getRequest()->getParam('month_key');
    }

    protected function getMonthLabel()
    {
        return $this->helperData->getDateFormat($this->getMonthKey() . '-10', true);
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        if ($breadcrumbs = $this->getLayout()->getBlock('breadcrumbs')) {
            $breadcrumbs->addCrumb($this->getMonthKey(), [
                    'label' => __('Monthy Archive'),
                    'title' => __('Monthy Archive')
                ]
            );
        }
    }

    public function getBlogTitle($meta = false)
    {
        $blogTitle = parent::getBlogTitle($meta);

        if ($meta) {
            array_push($blogTitle, $this->getMonthLabel());

            return $blogTitle;
        }

        return $this->getMonthLabel();
    }
}
