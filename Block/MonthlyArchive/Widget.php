<?php

namespace Baniwal\Blog\Block\MonthlyArchive;

use Magento\Cms\Model\Template\FilterProvider;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\View\Element\Template\Context;
use Baniwal\Blog\Block\Frontend;
use Baniwal\Blog\Helper\Data;
use Baniwal\Blog\Helper\Data as DataHelper;
use Baniwal\Blog\Model\CommentFactory;
use Baniwal\Blog\Model\LikeFactory;

class Widget extends Frontend
{
    public $dateTime;

    protected $_postDate;

    public function __construct(
        Context $context,
        FilterProvider $filterProvider,
        CommentFactory $commentFactory,
        LikeFactory $likeFactory,
        CustomerRepositoryInterface $customerRepository,
        DataHelper $helperData,
        DateTime $dateTime,
        array $data = []
    )
    {
        $this->dateTime = $dateTime;

        parent::__construct($context, $filterProvider, $commentFactory, $likeFactory, $customerRepository, $helperData, $data);
    }

    public function isEnable()
    {
        return $this->helperData->getBlogConfig('monthly_archive/enable_monthly');
    }

    public function getDateArrayCount()
    {
        return array_values(array_count_values($this->getDateArray()));
    }

    public function getDateArrayUnique()
    {
        return array_values(array_unique($this->getDateArray()));
    }

    public function getDateArray()
    {
        $dateArray = [];
        foreach ($this->getPostDate() as $postDate) {
            $dateArray[] = date("F Y", $this->dateTime->timestamp($postDate));
        }

        return $dateArray;
    }

    protected function getPostDate()
    {
        if (!$this->_postDate) {
            $posts = $this->helperData->getPostList();
            $postDates = [];
            if ($posts->getSize()) {
                foreach ($posts as $post) {
                    $postDates[] = $post->getPublishDate();
                }
            }
            $this->_postDate = $postDates;
        }

        return $this->_postDate;
    }

    public function getDateCount()
    {
        $limit = $this->helperData->getBlogConfig('monthly_archive/number_records') ?: 5;
        $dateArrayCount = $this->getDateArrayCount();
        $count = count($dateArrayCount);
        $result = ($count < $limit) ? $count : $limit;

        return $result;
    }

    public function getMonthlyUrl($month)
    {
        return $this->helperData->getBlogUrl($month, Data::TYPE_MONTHLY);
    }

    public function getDateLabel()
    {
        $postDates = $this->getPostDate();
        $postDatesLabel = [];
        if (sizeof($postDates)) {
            foreach ($postDates as $date) {
                $postDatesLabel[] = $this->helperData->getDateFormat($date, true);
            }
        }
        $result = array_values(array_unique($postDatesLabel));

        return $result;
    }
}
