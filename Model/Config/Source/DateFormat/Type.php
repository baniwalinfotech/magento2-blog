<?php

namespace Baniwal\Blog\Model\Config\Source\DateFormat;

use Magento\Framework\Option\ArrayInterface;

class Type implements ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */

    public function toOptionArray()
    {
        $dateArray = [];
        $type = [
            'F j, Y',
            'Y-m-d',
            'm/d/Y',
            'd/m/Y',
            'F j, Y g:i a',
            'F j, Y g:i A',
            'Y-m-d g:i a',
            'Y-m-d g:i A',
            'd/m/Y g:i a',
            'd/m/Y g:i A',
            'm/d/Y H:i',
            'd/m/Y H:i',
        ];
        foreach ($type as $item) {
            $dateArray[] = [
                'value' => $item,
                'label' => $item . ' (' . date($item) . ')'
            ];
        }

        return $dateArray;
    }
}
