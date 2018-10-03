<?php

namespace Baniwal\Blog\Block\Sidebar;

use Baniwal\Blog\Block\Frontend;

class MostView extends Frontend
{
    public function getMostViewPosts()
    {
        $collection = $this->helperData->getPostList();
        $collection->getSelect()
            ->joinLeft(
                ['traffic' => $collection->getTable('baniwal_blog_post_traffic')],
                'main_table.post_id=traffic.post_id',
                'numbers_view'
            )
            ->order('numbers_view DESC')
            ->limit((int)$this->helperData->getBlogConfig('sidebar/number_mostview_posts') ?: 4);

        return $collection;
    }

    public function getRecentPost()
    {
        $collection = $this->helperData->getPostList();
        $collection->getSelect()
            ->limit((int)$this->helperData->getBlogConfig('sidebar/number_recent_posts') ?: 4);

        return $collection;
    }
}
