<?php

namespace Baniwal\Blog\Model\Config\Source\Comments;

use Magento\Framework\Option\ArrayInterface;

class Type implements ArrayInterface
{
    const DEFAULT_COMMENT = 1;
    const FACEBOOK = 2;
    const DISQUS = 3;
    const DISABLE = 4;

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
            self::DEFAULT_COMMENT => __('Default Comment'),
            self::DISQUS => __('Disqus Comment'),
            self::FACEBOOK => __('Facebook Comment'),
            self::DISABLE => __('Disable Completely')
        ];
    }
}
