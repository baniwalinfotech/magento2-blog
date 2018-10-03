<?php

namespace Baniwal\Blog\Model\Config\Source\Import;

use Magento\Framework\Option\ArrayInterface;

class Behaviour implements ArrayInterface
{
    const UPDATE = "update";
    const REPLACE = "replace";
    const DELETE = "delete";

    /**
     * @return array
     */
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
            self::UPDATE => __('Add / Update'),
            self::REPLACE => __('Replace'),
            self::DELETE => __('Delete')
        ];
    }
}
