<?php

namespace Baniwal\Blog\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class WidgetShowType implements ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'new', 'label' => __('New')],
            ['value' => 'category', 'label' => __('Category')]
        ];
    }
}
