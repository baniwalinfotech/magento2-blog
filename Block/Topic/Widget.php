<?php

namespace Baniwal\Blog\Block\Topic;

use Baniwal\Blog\Block\Frontend;
use Baniwal\Blog\Helper\Data;

class Widget extends Frontend
{
    /**
     * @return array|string
     */
    public function getTopicList()
    {
        $collection = $this->helperData->getObjectList(Data::TYPE_TOPIC);

        return $collection;
    }

    /**
     * @param $topic
     * @return string
     */
    public function getTopicUrl($topic)
    {
        return $this->helperData->getBlogUrl($topic, Data::TYPE_TOPIC);
    }
}
