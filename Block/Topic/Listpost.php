<?php

namespace Baniwal\Blog\Block\Topic;

use Baniwal\Blog\Helper\Data;

class Listpost extends \Baniwal\Blog\Block\Listpost
{
    protected $_topic;

    protected function getCollection()
    {
        if ($topic = $this->getBlogObject()) {
            return $this->helperData->getPostCollection(Data::TYPE_TOPIC, $topic->getId());
        }

        return null;
    }

    /**
     * @return mixed
     */
    protected function getBlogObject()
    {
        if (!$this->_topic) {
            $id = $this->getRequest()->getParam('id');

            if ($id) {
                $topic = $this->helperData->getObjectByParam($id, null, Data::TYPE_TOPIC);
                if ($topic && $topic->getId()) {
                    $this->_topic = $topic;
                }
            }
        }

        return $this->_topic;
    }

    /**
     * @inheritdoc
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        if ($breadcrumbs = $this->getLayout()->getBlock('breadcrumbs')) {
            $topic = $this->getBlogObject();
            if ($topic) {
                $breadcrumbs->addCrumb($topic->getUrlKey(), [
                        'label' => __('Topic'),
                        'title' => __('Topic')
                    ]
                );
            }
        }
    }

    /**
     * @param bool $meta
     * @return array
     */
    public function getBlogTitle($meta = false)
    {
        $blogTitle = parent::getBlogTitle($meta);
        $topic = $this->getBlogObject();
        if (!$topic) {
            return $blogTitle;
        }

        if ($meta) {
            if ($topic->getMetaTitle()) {
                array_push($blogTitle, $topic->getMetaTitle());
            } else {
                array_push($blogTitle, ucfirst($topic->getName()));
            }

            return $blogTitle;
        }

        return ucfirst($topic->getName());
    }
}
