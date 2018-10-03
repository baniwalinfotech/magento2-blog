<?php

namespace Baniwal\Blog\Block\Adminhtml\Category\Edit\Tab;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Cms\Model\Wysiwyg\Config;
use Magento\Config\Model\Config\Source\Design\Robots;
use Magento\Config\Model\Config\Source\Enabledisable;
use Magento\Config\Model\Config\Source\Yesno;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Magento\Store\Model\System\Store;

class Category extends Generic implements TabInterface
{
    protected $wysiwygConfig;

    protected $booleanOptions;

    protected $enableDisable;

    protected $metaRobotsOptions;

    protected $systemStore;

    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Config $wysiwygConfig,
        Yesno $booleanOptions,
        Enabledisable $enableDisable,
        Robots $metaRobotsOptions,
        Store $systemStore,
        array $data = []
    )
    {
        $this->wysiwygConfig = $wysiwygConfig;
        $this->booleanOptions = $booleanOptions;
        $this->enableDisable = $enableDisable;
        $this->metaRobotsOptions = $metaRobotsOptions;
        $this->systemStore = $systemStore;

        parent::__construct($context, $registry, $formFactory, $data);
    }

    protected function _prepareForm()
    {
        $category = $this->_coreRegistry->registry('category');

        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('category_');
        $form->setFieldNameSuffix('category');

        $fieldset = $form->addFieldset('base_fieldset', [
                'legend' => __('Category Information'),
                'class' => 'fieldset-wide'
            ]
        );

        if (!$category->getId()) {
            $fieldset->addField('path', 'hidden', ['name' => 'path', 'value' => $this->getRequest()->getParam('parent') ?: 1]);
        } else {
            $fieldset->addField('category_id', 'hidden', ['name' => 'id', 'value' => $category->getId()]);
            $fieldset->addField('path', 'hidden', ['name' => 'path', 'value' => $category->getPath()]);
        }

        $fieldset->addField('name', 'text', [
                'name' => 'name',
                'label' => __('Name'),
                'title' => __('Name'),
                'required' => true,
            ]
        );
        $fieldset->addField('enabled', 'select', [
                'name' => 'enabled',
                'label' => __('Status'),
                'title' => __('Status'),
                'values' => $this->enableDisable->toOptionArray(),
            ]
        );

        if (!$this->_storeManager->isSingleStoreMode()) {

            $rendererBlock = $this->getLayout()->createBlock('Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset\Element');
            $fieldset->addField('store_ids', 'multiselect', [
                'name' => 'store_ids',
                'label' => __('Store Views'),
                'title' => __('Store Views'),
                'values' => $this->systemStore->getStoreValuesForForm(false, true)
            ])->setRenderer($rendererBlock);

            if (!$category->hasData('store_ids')) {
                $category->setStoreIds(0);
            }
        } else {
            $fieldset->addField('store_ids', 'hidden', [
                'name' => 'store_ids',
                'value' => $this->_storeManager->getStore()->getId()
            ]);
        }

        $fieldset->addField('url_key', 'text', [
                'name' => 'url_key',
                'label' => __('URL Key'),
                'title' => __('URL Key'),
            ]
        );
        $fieldset->addField('meta_title', 'text', [
                'name' => 'meta_title',
                'label' => __('Meta Title'),
                'title' => __('Meta Title'),
            ]
        );
        $fieldset->addField('meta_description', 'textarea', [
                'name' => 'meta_description',
                'label' => __('Meta Description'),
                'title' => __('Meta Description'),
            ]
        );
        $fieldset->addField('meta_keywords', 'textarea', [
                'name' => 'meta_keywords',
                'label' => __('Meta Keywords'),
                'title' => __('Meta Keywords'),
            ]
        );
        $fieldset->addField('meta_robots', 'select', [
                'name' => 'meta_robots',
                'label' => __('Meta Robots'),
                'title' => __('Meta Robots'),
                'values' => $this->metaRobotsOptions->toOptionArray(),
            ]
        );

        if (!$category->getId()) {
            $category->addData([
                'enabled' => 1,
                'meta_title' => $this->_scopeConfig->getValue('blog/seo/meta_title'),
                'meta_description' => $this->_scopeConfig->getValue('blog/seo/meta_description'),
                'meta_keywords' => $this->_scopeConfig->getValue('blog/seo/meta_keywords'),
                'meta_robots' => $this->_scopeConfig->getValue('blog/seo/meta_robots'),
            ]);
        }

        $form->addValues($category->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    public function getTabLabel()
    {
        return __('Category');
    }

    public function getTabTitle()
    {
        return $this->getTabLabel();
    }

    public function canShowTab()
    {
        return true;
    }

    public function isHidden()
    {
        return false;
    }
}
