<?php

namespace Baniwal\Blog\Controller\Adminhtml\Author;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Baniwal\Blog\Controller\Adminhtml\Author;
use Baniwal\Blog\Helper\Image;
use Baniwal\Blog\Model\AuthorFactory;

class Save extends Author
{
    protected $imageHelper;

    public function __construct(
        Context $context,
        Registry $registry,
        AuthorFactory $authorFactory,
        Image $imageHelper
    )
    {
        $this->imageHelper = $imageHelper;

        parent::__construct($context, $registry, $authorFactory);
    }

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($data = $this->getRequest()->getPost('author')) {

            $author = $this->initAuthor();

            $this->imageHelper->uploadImage($data, 'image', Image::TEMPLATE_MEDIA_TYPE_AUTH, $author->getImage());

            if (!empty($data)) {
                $author->addData($data);
            }

            $this->_eventManager->dispatch('baniwal_blog_author_prepare_save', ['author' => $author, 'request' => $this->getRequest()]);

            try {
                $author->save();

                $this->messageManager->addSuccess(__('The Author has been saved.'));
                $this->_getSession()->setData('baniwal_blog_author_data', false);

                if ($this->getRequest()->getParam('back')) {
                    $resultRedirect->setPath('baniwal_blog/*/edit', ['_current' => true]);
                } else {
                    $resultRedirect->setPath('baniwal_blog/*/');
                }

                return $resultRedirect;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the Author.'));
            }

            $this->_getSession()->setData('baniwal_blog_author_data', $data);

            $resultRedirect->setPath('baniwal_blog/*/edit', ['_current' => true]);

            return $resultRedirect;
        }
        $resultRedirect->setPath('baniwal_blog/*/');

        return $resultRedirect;
    }
}
