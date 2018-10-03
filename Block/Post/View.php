<?php

namespace Baniwal\Blog\Block\Post;

use Magento\Cms\Model\Template\FilterProvider;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Customer\Model\Url;
use Magento\Framework\View\Element\Template\Context;
use Baniwal\Blog\Helper\Data;
use Baniwal\Blog\Helper\Data as HelperData;
use Baniwal\Blog\Model\CategoryFactory;
use Baniwal\Blog\Model\CommentFactory;
use Baniwal\Blog\Model\LikeFactory;
use Baniwal\Blog\Model\Post;
use Baniwal\Blog\Model\PostFactory;

class View extends \Baniwal\Blog\Block\Listpost
{
    const LOGO = 'baniwal/blog/logo/';

    protected $categoryFactory;

    protected $postFactory;

    protected $customerUrl;

    protected $customerSession;

    public function __construct(
        Context $context,
        FilterProvider $filterProvider,
        CommentFactory $commentFactory,
        LikeFactory $likeFactory,
        CustomerRepositoryInterface $customerRepository,
        CustomerSession $customerSession,
        HelperData $helperData,
        Url $customerUrl,
        CategoryFactory $categoryFactory,
        PostFactory $postFactory,
        array $data = []
    )
    {
        $this->customerSession = $customerSession;
        $this->categoryFactory = $categoryFactory;
        $this->postFactory = $postFactory;
        $this->customerUrl = $customerUrl;

        parent::__construct($context, $filterProvider, $commentFactory, $likeFactory, $customerRepository, $helperData, $data);
    }

    protected function _construct()
    {
        parent::_construct();

        $post = $this->postFactory->create();
        if ($id = $this->getRequest()->getParam('id')) {
            $post->load($id);
        }
        $this->setPost($post);
    }

    protected function getBlogObject()
    {
        return $this->getPost();
    }

    public function isLoggedIn()
    {
        return $this->customerSession->isLoggedIn();
    }

    public function checkRss()
    {
        return $this->helperData->getBlogUrl('post/rss');
    }

    public function getTopicUrl($topic)
    {
        return $this->helperData->getBlogUrl($topic, Data::TYPE_TOPIC);
    }

    public function getTagUrl($tag)
    {
        return $this->helperData->getBlogUrl($tag, Data::TYPE_TAG);
    }

    public function getCategoryUrl($category)
    {
        return $this->helperData->getBlogUrl($category, Data::TYPE_CATEGORY);
    }

    public function helperComment($code)
    {
        return $this->helperData->getBlogConfig('comment/' . $code);
    }

    public function getCommentsHtml()
    {
        return $this->commentTree;
    }

    public function getUserComment($userId)
    {
        $user = $this->customerRepository->getById($userId);

        return $user;
    }

    public function getCommentLikes($cmtId)
    {
        $likes = $this->likeFactory->create()
            ->getCollection()
            ->addFieldToFilter('comment_id', $cmtId)
            ->getSize();

        return $likes ?: '';
    }

    public function isLiked($cmtId)
    {
        if ($this->customerSession->isLoggedIn()) {
            $customerData = $this->customerSession->getCustomerData();
            $customerId = $customerData->getId();
            $likes = $this->likeFactory->create()->getCollection();
            foreach ($likes as $like) {
                if ($like->getEntityId() == $customerId && $like->getCommentId() == $cmtId) {
                    return true;
                }
            }
        }

        return false;
    }

    public function getPostComments($postId)
    {
        $result = [];
        $comments = $this->cmtFactory->create()->getCollection()
            ->addFieldToFilter('main_table.post_id', $postId);
        foreach ($comments as $comment) {
            array_push($result, $comment->getData());
        }

        return $result;
    }

    public function getCommentsTree($comments, $cmtId)
    {
        $this->commentTree .= '<ul class="default-cmt__content__cmt-content row">';
        foreach ($comments as $comment) {

            if ($comment['reply_id'] == $cmtId && $comment['status'] == 1) {
                $isReply = (bool)$comment['is_reply'];
                $replyId = $isReply ? $comment['reply_id'] : '';
                if ($comment['entity_id'] == 0) {
                    $userName = $comment['user_name'];
                } else {
                    $userCmt = $this->getUserComment($comment['entity_id']);
                    $userName = $userCmt->getFirstName() . ' '
                        . $userCmt->getLastName();
                }
                $countLikes = $this->getCommentLikes($comment['comment_id']);
                $isLiked = ($this->isLiked($comment['comment_id'])) ? "baniwalblog-liked" : "baniwalblog-like";
                $this->commentTree .= '<li id="cmt-id-' . $comment['comment_id'] . '" class="default-cmt__content__cmt-content__cmt-row cmt-row-'.$comment['comment_id'].' cmt-row col-xs-12'
                    . ($isReply ? ' reply-row' : '') . '" data-cmt-id="'
                    . $comment['comment_id'] . '" ' . ($replyId
                        ? 'data-reply-id="' . $replyId . '"' : '') . '>
                                <div class="cmt-row__cmt-username">
                                    <span class="cmt-row__cmt-username username username__'.$comment['comment_id'].'">'
                    . $userName . '</span>
                                </div>
                                <div class="cmt-row__cmt-content">
                                    <p>' . $comment['content'] . '</p>
                                </div>
                                <div class="cmt-row__cmt-interactions interactions">
                                    <div class="interactions__btn-actions">
                                        <a class="interactions__btn-actions action btn-like ' . $isLiked . '" data-cmt-id="'
                    . $comment['comment_id'] . '" click="1">
                                        <i class="fa fa-thumbs-up" aria-hidden="true"></i>
                                        <span class="count-like__like-text">'
                    . $countLikes . '</span></a>
                                        <a class="interactions__btn-actions action btn-reply" data-cmt-id="'
                    . $comment['comment_id'] . '">' . __('Reply') . '</a>
                                    </div>
                                    <div class="interactions__cmt-createdat">
                                        <span>' . $this->getDateFormat($comment['created_at']) . '</span>
                                    </div>
                                </div>';
                if ($comment['has_reply']) {
                    $this->commentTree .= $this->getCommentsTree(
                        $comments,
                        $comment['comment_id']
                    );
                }
                $this->commentTree .= '</li>';
            }
        }
        $this->commentTree .= '</ul>';
    }

    public function getTagList($post)
    {
        $tagCollection = $post->getSelectedTagsCollection();
        $result = '';
        if (!empty($tagCollection)) {
            $listTags = [];
            foreach ($tagCollection as $tag) {
                $listTags[] = '<a class="baniwal-info" href="' . $this->getTagUrl($tag) . '">' . $tag->getName() . '</a>';
            }
            $result = implode(', ', $listTags);
        }

        return $result;
    }

    public function getLoginUrl()
    {
        return $this->customerUrl->getLoginUrl();
    }

    public function getRegisterUrl()
    {
        return $this->customerUrl->getRegisterUrl();
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        if ($breadcrumbs = $this->getLayout()->getBlock('breadcrumbs')) {
            if ($catId = $this->getRequest()->getParam('cat')) {
                $category = $this->categoryFactory->create()
                    ->load($catId);
                if ($category->getId()) {
                    $breadcrumbs->addCrumb($category->getUrlKey(), [
                            'label' => $category->getName(),
                            'title' => $category->getName(),
                            'link' => $this->helperData->getBlogUrl($category, Data::TYPE_CATEGORY)
                        ]
                    );
                }
            }

            $post = $this->getPost();
            $breadcrumbs->addCrumb($post->getUrlKey(), [
                    'label' => $post->getName(),
                    'title' => $post->getName()
                ]
            );
        }
    }

    public function getBlogTitle($meta = false)
    {
        $blogTitle = parent::getBlogTitle($meta);
        $post = $this->getBlogObject();
        if (!$post) {
            return $blogTitle;
        }

        if ($meta) {
            if ($post->getMetaTitle()) {
                array_push($blogTitle, $post->getMetaTitle());
            } else {
                array_push($blogTitle, ucfirst($post->getName()));
            }

            return $blogTitle;
        }

        return ucfirst($post->getName());
    }

    public function getMessagesHtml($priority, $message)
    {
        /** @var $messagesBlock \Magento\Framework\View\Element\Messages */
        $messagesBlock = $this->_layout->createBlock(\Magento\Framework\View\Element\Messages::class);
        $messagesBlock->{$priority}(__($message));

        return $messagesBlock->toHtml();
    }
}
