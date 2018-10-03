<?php

namespace Baniwal\Blog\Block\Sidebar;

use Baniwal\Blog\Block\Frontend;
use Baniwal\Blog\Helper\Data;

class Search extends Frontend
{
    public function getSearchBlogData()
    {
        $result = [];
        $posts = $this->helperData->getPostList();
        $limitDesc = (int)$this->getSidebarConfig('search/description');
        if (!empty($posts)) {
            foreach ($posts as $item) {
                $shortDescription = ($item->getShortDescription() && $limitDesc > 0) ? $item->getShortDescription() : '';
                if (strlen($shortDescription) > $limitDesc) {
                    $shortDescription = mb_substr($shortDescription, 0, $limitDesc, 'UTF-8') . '...';
                }

                $result[] = [
                    'value' => $item->getName(),
                    'url' => $item->getUrl(),
                    'image' => $this->resizeImage($item->getImage(), '100x'),
                    'desc' => $shortDescription
                ];
            }
        }

        return Data::jsonEncode($result);
    }

    public function getSidebarConfig($code, $storeId = null)
    {
        return $this->helperData->getBlogConfig('sidebar/' . $code, $storeId);
    }
}
