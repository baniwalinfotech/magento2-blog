<?php

namespace Baniwal\Blog\Model\Config\Source\Comments\Facebook;

use Magento\Framework\Option\ArrayInterface;

class Colorscheme implements ArrayInterface
{
    const LIGHT = 'light';
    const DARK = 'dark';

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
        return [self::LIGHT => __('Light'), self::DARK => __('Dark')];
    }

    public function getAllOptions()
    {
        return $this->toOptionArray();
    }
}
