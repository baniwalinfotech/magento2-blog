<?php

namespace Baniwal\Blog\Controller\Post;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\Url as CustomerUrl;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\Json\Helper\Data as JsonData;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\StoreManagerInterface;
use Baniwal\Blog\Helper\Data;
use Baniwal\Blog\Helper\Data as HelperBlog;
use Baniwal\Blog\Model\CommentFactory;
use Baniwal\Blog\Model\Config\Source\Comments\Status;
use Baniwal\Blog\Model\LikeFactory;
use Baniwal\Blog\Model\PostFactory;
use Baniwal\Blog\Model\TrafficFactory;

class View extends Action
{
    const COMMENT = 1;
    const LIKE = 2;

    protected $trafficFactory;

    protected $resultPageFactory;

    protected $helperBlog;

    protected $accountManagement;

    protected $customerUrl;

    protected $session;

    protected $storeManager;

    protected $jsonHelper;

    protected $cmtFactory;

    protected $likeFactory;

    protected $dateTime;

    protected $timeZone;

    protected $resultForwardFactory;

    protected $postFactory;

    public function __construct(
        Context $context,
        ForwardFactory $resultForwardFactory,
        StoreManagerInterface $storeManager,
        JsonData $jsonHelper,
        CommentFactory $commentFactory,
        LikeFactory $likeFactory,
        DateTime $dateTime,
        TimezoneInterface $timezone,
        HelperBlog $helperBlog,
        PageFactory $resultPageFactory,
        AccountManagementInterface $accountManagement,
        CustomerUrl $customerUrl,
        Session $customerSession,
        TrafficFactory $trafficFactory,
        PostFactory $postFactory
    )
    {
        parent::__construct($context);

        $this->storeManager = $storeManager;
        $this->helperBlog = $helperBlog;
        $this->resultPageFactory = $resultPageFactory;
        $this->accountManagement = $accountManagement;
        $this->customerUrl = $customerUrl;
        $this->session = $customerSession;
        $this->timeZone = $timezone;
        $this->trafficFactory = $trafficFactory;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->jsonHelper = $jsonHelper;
        $this->cmtFactory = $commentFactory;
        $this->likeFactory = $likeFactory;
        $this->dateTime = $dateTime;
        $this->postFactory = $postFactory;
    }

    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $post = $this->helperBlog->getFactoryByType(Data::TYPE_POST)->create()->load($id);
        if (!$post->getEnabled()) {
            return $this->resultForwardFactory->create()->forward('noroute');
        }

        $trafficModel = $this->trafficFactory->create()->load($id, 'post_id');
        if ($trafficModel->getId()) {
            $trafficModel->setNumbersView($trafficModel->getNumbersView() + 1);
            $trafficModel->save();
        } else {
            $traffic = $this->trafficFactory->create();
            $traffic->addData(['post_id' => $id, 'numbers_view' => 1])->save();
        }

        if ($this->getRequest()->isAjax()) {
            $params = $this->getRequest()->getParams();
            $result = [];
            if ($this->session->isLoggedIn()) {
                $customerData = $this->session->getCustomerData();
                $user = [
                    "user_id" => $customerData->getId(),
                    "first_name" => $customerData->getFirstname(),
                    "last_name" => $customerData->getLastname()
                ];
            } else {
                $user = [
                    "user_id" => 0,
                    "first_name" => $params["guestName"],
                    "last_name" => "",
                    "email" => $params["guestEmail"]
                ];
                if (!$this->accountManagement->isEmailAvailable($user["email"], $this->storeManager->getWebsite()->getId())) {
                    $result = ['status' => 'duplicated'];
                    return $this->getResponse()->representJson($this->jsonHelper->jsonEncode($result));
                }
            }
            if (isset($params['cmt_text'])) {
                $cmtText = $params['cmt_text'];
                $isReply = isset($params['isReply']) ? $params['isReply'] : 0;
                $replyId = isset($params['replyId']) ? $params['replyId'] : 0;
                $commentData = [
                    'post_id' => $id, '',
                    'entity_id' => $user["user_id"],
                    'is_reply' => $isReply,
                    'reply_id' => $replyId,
                    'content' => $cmtText,
                    'created_at' => $this->dateTime->date(),
                    'status' => $this->helperBlog->getBlogConfig('comment/need_approve') ? Status::PENDING : Status::APPROVED,
                    'store_ids' => $this->storeManager->getStore()->getId(),
                ];
                if ($user["user_id"] == '0') {
                    $commentData['user_name'] = $user['first_name'];
                    $commentData['user_email'] = $user['email'];
                }
                $commentModel = $this->cmtFactory->create();
                $result = $this->commentActions(self::COMMENT, $user, $commentData, $commentModel);
            }

            if (isset($params['cmtId'])) {
                $cmtId = $params['cmtId'];
                $likeData = [
                    'comment_id' => $cmtId,
                    'entity_id' => $user["user_id"]
                ];

                $likeModel = $this->likeFactory->create();
                $result = $this->commentActions(self::LIKE, $user, $likeData, $likeModel, $cmtId);
            }

            return $this->getResponse()->representJson($this->jsonHelper->jsonEncode($result));
        }

        return $this->resultPageFactory->create();
    }

    public function commentActions($action, $user, $data, $model, $cmtId = null)
    {
        try {
            switch ($action) {
                //comment action
                case self::COMMENT:
                    $model->addData($data)->save();
                    $cmtHasReply = $model->getCollection()
                        ->addFieldToFilter('comment_id', $data['reply_id'])
                        ->getFirstItem();
                    if ($cmtHasReply->getId()) {
                        $cmtHasReply->setHasReply(1)->save();
                    }

                    $lastCmt = $model->getCollection()->setOrder('comment_id', 'desc')->getFirstItem();
                    $lastCmtId = $lastCmt !== null ? $lastCmt->getId() : 1;
                    $result = [
                        'cmt_id' => $lastCmtId,
                        'cmt_text' => $data['content'],
                        'user_cmt' => $user['first_name'] . ' ' . $user['last_name'],
                        'is_reply' => $data['is_reply'],
                        'reply_cmt' => $data['reply_id'],
                        'created_at' => __('Just now'),
                        'status' => $data['status']
                    ];
                    break;
                //like action
                case self::LIKE:
                    $checkLike = $this->isLikedComment($cmtId, $user['user_id'], $model);
                    if (!$checkLike) {
                        $model->addData($data)->save();
                    }
                    $likes = $model->getCollection()->addFieldToFilter('comment_id', $cmtId);
                    $countLikes = ($likes->getSize()) ? $likes->getSize() : '';
                    $isLiked = ($checkLike) ? "yes" : "no";
                    $result = [
                        'liked' => $isLiked,
                        'comment_id' => $cmtId,
                        'count_like' => $countLikes,
                        'status' => 'ok'
                    ];
                    break;
                default:
                    $result = ['status' => 'error', 'error' => __('Action not found.')];
                    break;
            }
        } catch (\Exception $e) {
            $result = ['status' => 'error', 'error' => $e->getMessage()];
        }

        return $result;
    }

    public function isLikedComment($cmtId, $userId, $model)
    {
        $liked = $model->getCollection()->addFieldToFilter('comment_id', $cmtId);
        foreach ($liked as $item) {
            if ($item->getEntityId() == $userId) {
                try {
                    $item->delete();
                    return true;
                } catch (\Exception $e) {
                    return false;
                }
            }
        }

        return false;
    }
}
