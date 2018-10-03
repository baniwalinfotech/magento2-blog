<?php

namespace Baniwal\Blog\Controller\Adminhtml\Import;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Baniwal\Blog\Model\Import\WordPress;
use Baniwal\Blog\Model\Import\AheadWorksM1;
use Baniwal\Blog\Model\Import\MageFanM2;
use Baniwal\Blog\Helper\Data as BlogHelper;

class Import extends Action
{
    protected $_wordpressModel;

    protected $_aheadWorksM1Model;

    protected $_mageFanM2Model;

    public $blogHelper;

    public $registry;

    public function __construct(
        Action\Context $context,
        WordPress $wordPress,
        AheadWorksM1 $aheadWorksM1,
        MageFanM2 $mageFanM2,
        BlogHelper $blogHelper,
        Registry $registry
    )
    {
        $this->blogHelper = $blogHelper;
        $this->_wordpressModel = $wordPress;
        $this->_aheadWorksM1Model = $aheadWorksM1;
        $this->_mageFanM2Model = $mageFanM2;
        $this->registry = $registry;
        parent::__construct($context);
    }

    public function execute()
    {
        $data = $this->_getSession()->getData('baniwal_blog_import_data');
        switch ($data['type']) {
            case 'wordpress':
                $response = $this->processImport($this->_wordpressModel, $data);
                break;
            case 'aheadworksm1':
                $response = $this->processImport($this->_aheadWorksM1Model, $data);
                break;
            case 'magefan':
                $response = $this->processImport($this->_mageFanM2Model, $data);
                break;
            default:
                $response = $this->processImport($this->_wordpressModel, $data);
        }
        return $response;
    }

    protected function getStatistic($statisticData, $messagesBlock, $data)
    {
        switch ($data['behaviour']) {
            case 'replace':
                $statisticHtml = $messagesBlock
                    ->{'addsuccess'}(__('You have replaced and updated %1 %2 successful. Skipped %3 %2.',
                        $statisticData['success_count'],
                        $statisticData['type'],
                        $statisticData['error_count']
                    ))
                    ->toHtml();
                break;

            case 'delete':
                $statisticHtml = $messagesBlock
                    ->{'addsuccess'}(__('You have deleted %1 successful.',
                        $statisticData['type']
                    ))
                    ->toHtml();
                break;
            default:
                $statisticHtml = $messagesBlock
                    ->{'addsuccess'}(__('You have imported %1 %2 successful. Skipped %3 %2.',
                        $statisticData['success_count'],
                        $statisticData['type'],
                        $statisticData['error_count']
                    ))
                    ->toHtml();
        }
        return $statisticHtml;
    }

    protected function processImport($object, $data)
    {
        $statisticHtml = '';
        $connection = mysqli_connect($data['host'], $data['user_name'], $data['password'], $data['database']);
        $messagesBlock = $this->_view->getLayout()->createBlock(\Magento\Framework\View\Element\Messages::class);
        if ($object->run($data, $connection)) {

            $postStatistic = $this->registry->registry('baniwal_import_post_statistic');
            if ($postStatistic['has_data']) {
                $statisticHtml = $this->getStatistic($postStatistic, $messagesBlock, $data);
            }

            $tagStatistic = $this->registry->registry('baniwal_import_tag_statistic');
            if ($tagStatistic['has_data']) {
                $statisticHtml = $this->getStatistic($tagStatistic, $messagesBlock, $data);
            }

            $categoryStatistic = $this->registry->registry('baniwal_import_category_statistic');
            if ($categoryStatistic['has_data']) {
                $statisticHtml = $this->getStatistic($categoryStatistic, $messagesBlock, $data);
            }

            $authorStatistic = $this->registry->registry('baniwal_import_user_statistic');
            if ($authorStatistic['has_data']) {
                $statisticHtml = $this->getStatistic($authorStatistic, $messagesBlock, $data);
            }

            $commentStatistic = $this->registry->registry('baniwal_import_comment_statistic');
            if ($commentStatistic['has_data']) {
                $statisticHtml = $this->getStatistic($commentStatistic, $messagesBlock, $data);
            }

            if ($statisticHtml == '' && $data['behaviour'] == 'update') {
                $statisticHtml = $messagesBlock
                    ->{'addsuccess'}(__('There are no records are updated.'))
                    ->toHtml();
            }

            $result = ['statistic' => $statisticHtml, 'status' => 'ok'];
            mysqli_close($connection);
            return $this->getResponse()->representJson(BlogHelper::jsonEncode($result));
        } else {
            $statisticHtml = $messagesBlock
                ->{'adderror'}(__('Can not make import, please check your table prefix OR import type and try again.'))
                ->toHtml();
            $result = ['statistic' => $statisticHtml, 'status' => 'ok'];
            return $this->getResponse()->representJson(BlogHelper::jsonEncode($result));
        }
    }
}
