<?php

namespace Baniwal\Blog\Controller\Post;

use Magento\Framework\Exception\NotFoundException;
use Magento\Rss\Controller\Feed;

class Rss extends Feed
{
    /**
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        $type = 'blog_posts';
        try {
            $provider = $this->rssManager->getProvider($type);
        } catch (\InvalidArgumentException $e) {
            throw new NotFoundException(__($e->getMessage()));
        }

        if ($provider->isAuthRequired() && !$this->auth()) {
            return;
        }

        /** @var $rss \Magento\Rss\Model\Rss */
        $rss = $this->rssFactory->create();
        $rss->setDataProvider($provider);

        $this->getResponse()->setHeader('Content-type', 'text/xml; charset=UTF-8');
        $this->getResponse()->setBody($rss->createRssXml());
    }
}
