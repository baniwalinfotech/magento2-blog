<?php

namespace Baniwal\Blog\Controller\Adminhtml\Import;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Baniwal\Blog\Helper\Data as BlogHelper;
use Magento\Framework\Exception\LocalizedException;

class Validate extends Action
{
    public $blogHelper;

    public function __construct(
        Context $context,
        BlogHelper $blogHelper
    )
    {
        $this->blogHelper = $blogHelper;
        parent::__construct($context);
    }

    public function execute()
    {
        $data = $this->getRequest()->getParams();

        try {
            $connect = mysqli_connect($data['host'], $data['user_name'], $data['password'], $data['database']);
            $importName = $data['import_name'];

            /** @var \Magento\Backend\Model\Session */
            $this->_getSession()->setData('baniwal_blog_import_data', $data);
            $result = ['import_name' => $importName, 'status' => 'ok'];

            mysqli_close($connect);
            return $this->getResponse()->representJson(BlogHelper::jsonEncode($result));
        } catch (LocalizedException $e) {
            $result = ['import_name' => $data["import_name"], 'status' => 'false'];
            return $this->getResponse()->representJson(BlogHelper::jsonEncode($result));
        } catch (\RuntimeException $e) {
            $result = ['import_name' => $data["import_name"], 'status' => 'false'];
            return $this->getResponse()->representJson(BlogHelper::jsonEncode($result));
        } catch (\Exception $e) {
            $result = ['import_name' => $data["import_name"], 'status' => 'false'];
            return $this->getResponse()->representJson(BlogHelper::jsonEncode($result));
        }
    }
}
