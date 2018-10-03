<?php

namespace Baniwal\Blog\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class SideBarLR implements ArrayInterface
{
    const LEFT = 0;
    const RIGHT = 1;

    public function toOptionArray()
    {
        $options = [];
        foreach ($this->toArray() as $value => $label) {
            $options[] = [
                'value' => $value,
                'label' => $label
            ];
        }

        return $options;
    }

    public function toArray()
    {
        return [
            self::LEFT => __('Left'),
            self::RIGHT => __('Right')
        ];
    }
}
