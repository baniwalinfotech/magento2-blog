<?php

namespace Baniwal\Blog\Block;

use Magento\Cms\Model\Template\FilterProvider;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Baniwal\Blog\Helper\Data;
use Baniwal\Blog\Helper\Data as HelperData;
use Baniwal\Blog\Helper\Image;
use Baniwal\Blog\Model\CommentFactory;
use Baniwal\Blog\Model\LikeFactory;

class Frontend extends Template
{
    /**
     * @var FilterProvider
     */
    public $filterProvider;

    public $helperData;

    public $store;

    public $cmtFactory;

    public $likeFactory;

    public $customerRepository;

    public $commentTree;

    public function __construct(
        Context $context,
        FilterProvider $filterProvider,
        CommentFactory $commentFactory,
        LikeFactory $likeFactory,
        CustomerRepositoryInterface $customerRepository,
        HelperData $helperData,
        array $data = []
    )
    {
        $this->filterProvider = $filterProvider;
        $this->cmtFactory = $commentFactory;
        $this->likeFactory = $likeFactory;
        $this->customerRepository = $customerRepository;
        $this->helperData = $helperData;
        $this->store = $context->getStoreManager();

        parent::__construct($context, $data);
    }

    public function getPageFilter($content)
    {
        return $this->filterProvider->getPageFilter()->filter($content);
    }

    public function getImageUrl($image, $type = Image::TEMPLATE_MEDIA_TYPE_POST)
    {
        $imageHelper = $this->helperData->getImageHelper();
        $imageFile = $imageHelper->getMediaPath($image, $type);

        return $this->helperData->getImageHelper()->getMediaUrl($imageFile);
    }

    public function getRssUrl($urlKey, $type = null)
    {
        if (is_object($urlKey)) {
            $urlKey = $urlKey->getUrlKey();
        }

        $urlKey = ($type ? $type . '/' : '') . $urlKey;
        $url = $this->helperData->getUrl($this->helperData->getRoute() . '/' . $urlKey);

        return rtrim($url, '/') . '.xml';
    }

    public function getPostInfo($post)
    {
        $html = __('Posted on %1', $this->getDateFormat($post->getPublishDate()));

        if ($categoryPost = $this->getPostCategoryHtml($post)) {
            $html .= __('| Posted in %1', $categoryPost);
        }

        $author = $this->helperData->getAuthorByPost($post);
        if ($author && $author->getName() && $this->helperData->showAuthorInfo()) {
            $aTag = '<a class="baniwal-info" href="' . $author->getUrl() . '">' . $this->escapeHtml($author->getName()) . '</a>';
            $html .= __('| By: %1', $aTag);
        }

        return $html;
    }

    public function getPostCategoryHtml($post)
    {
        if (!$post->getCategoryIds()) {
            return null;
        }

        $categories = $this->helperData->getCategoryCollection($post->getCategoryIds());
        $categoryHtml = [];
        foreach ($categories as $_cat) {
            $categoryHtml[] = '<a class="baniwal-info" href="' . $this->helperData->getBlogUrl($_cat, Data::TYPE_CATEGORY) . '">' . $_cat->getName() . '</a>';
        }
        $result = implode(', ', $categoryHtml);

        return $result;
    }

    public function getDateFormat($date, $monthly = false)
    {
        return $this->helperData->getDateFormat($date, $monthly);
    }

    public function resizeImage($image, $size = null, $type = Image::TEMPLATE_MEDIA_TYPE_POST)
    {
        if (!$image) {
            return $this->getDefaultImageUrl();
        }

        return $this->helperData->getImageHelper()->resizeImage($image, $size, $type);
    }

    public function getDefaultImageUrl()
    {
        return $this->getViewFileUrl('Baniwal_Blog::media/images/baniwal-logo-default.png');
    }

    public function getDefaultAuthorImage()
    {
        return $this->getViewFileUrl('Baniwal_Blog::media/images/no-artist-image.jpg');
    }
}
