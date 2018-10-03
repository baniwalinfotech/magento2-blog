<?php

namespace Baniwal\Blog\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Baniwal\Blog\Helper\Data;

class Author extends AbstractDb
{
    public $helperData;

    protected $_isPkAutoIncrement = false;

    public function __construct(
        Context $context,
        Data $helperData
    )
    {
        $this->helperData = $helperData;
        parent::__construct($context);
    }

    protected function _construct()
    {
        $this->_init('baniwal_blog_author', 'user_id');
    }

    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        $object->setUrlKey(
            $this->helperData->generateUrlKey($this, $object, $object->getUrlKey() ?: $object->getName())
        );

        if (!$object->isObjectNew()) {
            $object->setUpdatedAt(\Zend_Date::now());
        }

        return $this;
    }
}
