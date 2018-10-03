<?php

namespace Baniwal\Blog\Controller\Adminhtml\Post;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Helper\Js;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Baniwal\Blog\Controller\Adminhtml\Post;
use Baniwal\Blog\Helper\Image;
use Baniwal\Blog\Model\PostFactory;
use Magento\Framework\Stdlib\DateTime\DateTime;

class Save extends Post
{
    public $jsHelper;

    public $date;
  
    protected $imageHelper;

    public function __construct(
        Context $context,
        Registry $registry,
        PostFactory $postFactory,
        Js $jsHelper,
        Image $imageHelper,
        DateTime $date
    )
    {
        $this->jsHelper = $jsHelper;
        $this->imageHelper = $imageHelper;
        $this->date = $date;
        parent::__construct($postFactory, $registry, $context);
    }

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($data = $this->getRequest()->getPost('post')) {

            $post = $this->initPost();
            $this->prepareData($post, $data);

            $this->_eventManager->dispatch('baniwal_blog_post_prepare_save', ['post' => $post, 'request' => $this->getRequest()]);

            try {
                $post->save();

                $this->messageManager->addSuccess(__('The post has been saved.'));
                $this->_getSession()->setData('baniwal_blog_post_data', false);

                if ($this->getRequest()->getParam('back')) {
                    $resultRedirect->setPath('baniwal_blog/*/edit', ['id' => $post->getId(), '_current' => true]);
                } else {
                    $resultRedirect->setPath('baniwal_blog/*/');
                }

                return $resultRedirect;
            } catch (LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the Post.'));
            }

            $this->_getSession()->setData('baniwal_blog_post_data', $data);

            $resultRedirect->setPath('baniwal_blog/*/edit', ['id' => $post->getId(), '_current' => true]);

            return $resultRedirect;
        }

        $resultRedirect->setPath('baniwal_blog/*/');

        return $resultRedirect;
    }

    protected function prepareData($post, $data = [])
    {
        $this->imageHelper->uploadImage($data, 'image', Image::TEMPLATE_MEDIA_TYPE_POST, $post->getImage());

        /** Set specify field data */
        $timezone = $this->_objectManager->create('Magento\Framework\Stdlib\DateTime\TimezoneInterface');
        $data['publish_date'] .= ' '.$data['publish_time'][0].':'.$data['publish_time'][1].':'.$data['publish_time'][2];
        $data['publish_date'] = $timezone->convertConfigTimeToUtc(isset($data['publish_date']) ? $data['publish_date'] : null);
        $data['modifier_id'] = $this->_auth->getUser()->getId();
        $data['categories_ids'] = (isset($data['categories_ids']) && $data['categories_ids']) ? explode(',', $data['categories_ids']) : [];
        $data['tags_ids'] = (isset($data['tags_ids']) && $data['tags_ids']) ? explode(',', $data['tags_ids']) : [];
        $data['topics_ids'] = (isset($data['topics_ids']) && $data['topics_ids']) ? explode(',', $data['topics_ids']) : [];

        if ($post->getCreatedAt() == null) {
            $data['created_at'] = $this->date->date();
        }
        $data['updated_at'] = $this->date->date();

        $post->addData($data);

        if ($tags = $this->getRequest()->getPost('tags', false)) {
            $post->setTagsData(
                $this->jsHelper->decodeGridSerializedInput($tags)
            );
        }

        if ($topics = $this->getRequest()->getPost('topics', false)) {
            $post->setTopicsData(
                $this->jsHelper->decodeGridSerializedInput($topics)
            );
        }

        if ($products = $this->getRequest()->getPost('products', false)) {
            $post->setProductsData(
                $this->jsHelper->decodeGridSerializedInput($products)
            );
        }

        return $this;
    }
}