<?php

namespace Baniwal\Blog\Block\Post\Rss;

use Magento\Framework\App\Rss\DataProviderInterface;
use Magento\Framework\App\Rss\UrlBuilderInterface;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\Element\Template\Context;
use Baniwal\Blog\Helper\Data;

class Lists extends AbstractBlock implements DataProviderInterface
{
    public $rssUrlBuilder;

    public $storeManager;

    public $helper;

    public function __construct(
        Context $context,
        UrlBuilderInterface $rssUrlBuilder,
        Data $helper,
        array $data = []
    )
    {
        $this->rssUrlBuilder = $rssUrlBuilder;
        $this->helper = $helper;
        $this->storeManager = $context->getStoreManager();

        parent::__construct($context, $data);
    }

    protected function _construct()
    {
        $this->setCacheKey('rss_blog_posts_store_' . $this->getStoreId());

        parent::_construct();
    }

    public function isAllowed()
    {
        return $this->helper->isEnabled();
    }

    public function getRssData()
    {
        $storeModel = $this->storeManager->getStore($this->getStoreId());
        $title = __('List Posts from %1', $storeModel->getFrontendName());
        $data = [
            'title' => $title,
            'description' => $title,
            'link' => $this->rssUrlBuilder->getUrl(['store_id' => $this->getStoreId(), 'type' => 'blog_posts']),
            'charset' => 'UTF-8',
            'language' => $this->helper->getConfigValue('general/locale/code', $storeModel),
        ];

        $posts = $this->helper->getPostList($this->getStoreId())
            ->addFieldToFilter('in_rss', 1)
            ->setOrder('post_id', 'DESC');
        $posts->getSelect()
            ->limit(10);
        foreach ($posts as $item) {
            $item->setAllowedInRss(true);
            $item->setAllowedPriceInRss(true);

            $description = '<table><tr><td style="text-decoration:none;"> ' . $item->getShortDescription() . '</td></tr></table>';
            $data['entries'][] = [
                'title' => $item->getName(),
                'link' => $item->getUrl(),
                'description' => $description,
                'lastUpdate' => strtotime($item->getPublishDate())
            ];
        }

        return $data;
    }

    public function getStoreId()
    {
        $storeId = (int)$this->getRequest()->getParam('store_id');
        if ($storeId == null) {
            $storeId = $this->storeManager->getStore()->getId();
        }

        return $storeId;
    }

    public function getCacheLifetime()
    {
        return 1;
    }

    public function getFeeds()
    {
        $data = [];
        if ($this->isAllowed()) {
            $url = $this->rssUrlBuilder->getUrl(['type' => 'blog_posts']);
            $data = ['label' => __('Posts'), 'link' => $url];
        }

        return $data;
    }

    public function isAuthRequired()
    {
        return false;
    }
}
