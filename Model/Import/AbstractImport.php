<?php

namespace Baniwal\Blog\Model\Import;

use Magento\Framework\Model\Context;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Baniwal\Blog\Model\PostFactory;
use Baniwal\Blog\Model\TagFactory;
use Baniwal\Blog\Model\CategoryFactory;
use Baniwal\Blog\Model\TopicFactory;
use Baniwal\Blog\Model\CommentFactory;
use Magento\User\Model\UserFactory;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Backend\Model\Auth\Session;
use Baniwal\Blog\Helper\Image as HelperImage;
use Baniwal\Blog\Helper\Data as HelperData;
use Baniwal\Blog\Model\Config\Source\Import\Type;

abstract class AbstractImport extends AbstractModel
{
    public $date;

    public $importType;

    public $helperData;

    protected $_postFactory;

    protected $_tagFactory;

    protected $_categoryFactory;

    protected $_topicFactory;
   
    protected $_commentFactory;

    protected $_userFactory;

    protected $_customerFactory;

    protected $_storeManager;

    protected $_helperImage;

    protected $_objectManager;

    protected $_resourceConnection;

    protected $_authSession;

    protected $_errorCount = 0;

    protected $_successCount = 0;

    protected $_hasData = false;

    protected $_type;

    public function __construct(
        Context $context,
        Registry $registry,
        PostFactory $postFactory,
        TagFactory $tagFactory,
        CategoryFactory $categoryFactory,
        TopicFactory $topicFactory,
        CommentFactory $commentFactory,
        UserFactory $userFactory,
        CustomerFactory $customerFactory,
        ObjectManagerInterface $objectManager,
        Session $authSession,
        ResourceConnection $resourceConnection,
        DateTime $date,
        Type $importType,
        HelperData $helperData,
        StoreManagerInterface $storeManager,
        HelperImage $helperImage,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    )
    {
        $this->date = $date;
        $this->importType = $importType;
        $this->_type = $this->_getImportType();
        $this->helperData = $helperData;
        $this->_postFactory = $postFactory;
        $this->_tagFactory = $tagFactory;
        $this->_categoryFactory = $categoryFactory;
        $this->_topicFactory = $topicFactory;
        $this->_commentFactory = $commentFactory;
        $this->_userFactory = $userFactory;
        $this->_customerFactory = $customerFactory;
        $this->_objectManager = $objectManager;
        $this->_resourceConnection = $resourceConnection;
        $this->_authSession = $authSession;
        $this->_storeManager = $storeManager;
        $this->_helperImage = $helperImage;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    abstract protected function _importPosts($data, $connection);

    abstract protected function _importTags($data, $connection);

    abstract protected function _importCategories($data, $connection);

    abstract protected function _importComments($data, $connection);

    abstract protected function _importAuthors($data, $connection);

    protected function _getStatistics($type, $successCount, $errorCount, $hasData)
    {
        $statistics = [
            "type" => $type,
            "success_count" => $successCount,
            "error_count" => $errorCount,
            "has_data" => $hasData
        ];
        return $statistics;
    }

    protected function _resetRecords()
    {
        $this->_errorCount = 0;
        $this->_successCount = 0;
        $this->_hasData = false;
    }

    protected function _generatePassword($length = 9, $add_dashes = false, $available_sets = 'luds')
    {
        $sets = array();
        if (strpos($available_sets, 'l') !== false)
            $sets[] = 'abcdefghjkmnpqrstuvwxyz';
        if (strpos($available_sets, 'u') !== false)
            $sets[] = 'ABCDEFGHJKMNPQRSTUVWXYZ';
        if (strpos($available_sets, 'd') !== false)
            $sets[] = '23456789';
        if (strpos($available_sets, 's') !== false)
            $sets[] = '!@#$%&*?';
        $all = '';
        $password = '';
        foreach ($sets as $set) {
            $password .= $set[array_rand(str_split($set))];
            $all .= $set;
        }
        $all = str_split($all);
        for ($i = 0; $i < $length - count($sets); $i++)
            $password .= $all[array_rand($all)];
        $password = str_shuffle($password);
        if (!$add_dashes)
            return $password;
        $dash_len = floor(sqrt($length));
        $dash_str = '';
        while (strlen($password) > $dash_len) {
            $dash_str .= substr($password, 0, $dash_len) . '-';
            $password = substr($password, $dash_len);
        }
        $dash_str .= $password;
        return $dash_str;
    }

    protected function _getImportType()
    {
        $types = [];
        foreach ($this->importType->toOptionArray() as $item) {
            $types[$item['value']] = $item['value'];
        }
        return $types;
    }
}
