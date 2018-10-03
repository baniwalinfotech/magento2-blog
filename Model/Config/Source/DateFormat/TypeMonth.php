<?php

namespace Baniwal\Blog\Model\Config\Source\DateFormat;

use Magento\Framework\Option\ArrayInterface;

class TypeMonth implements ArrayInterface
{
    public function toOptionArray()
    {
        $dateArray = [];
        $type = ['F , Y', 'Y - m', 'm / Y', 'M  Y'];
        foreach ($type as $item) {
            $dateArray [] = [
                'value' => $item,
                'label' => $item . ' (' . date($item) . ')'
            ];
        }

        return $dateArray;
    }
}
