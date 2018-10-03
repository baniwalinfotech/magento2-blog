<?php

namespace Baniwal\Blog\Block\Adminhtml\Import\Edit;

use Magento\Backend\Block\Template\Context;
use Magento\Cms\Model\Wysiwyg\Config;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Magento\Store\Model\System\Store;
use Baniwal\Blog\Helper\Image as ImageHelper;
use Baniwal\Blog\Model\Config\Source\Import\Type;
use Baniwal\Blog\Model\Config\Source\Import\Behaviour;
use Magento\Config\Model\Config\Source\Yesno;
use Magento\Backend\Block\Widget\Form\Generic;

class Form extends Generic
{
    public $systemStore;

    public $wysiwygConfig;

    public $booleanOptions;

    protected $_imageHelper;

    protected $_importType;

    protected $_importBehaviour;

    public function __construct(
        Config $wysiwygConfig,
        Store $systemStore,
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        ImageHelper $imageHelper,
        Type $importType,
        Behaviour $importBehaviour,
        Yesno $booleanOptions,
        array $data = []
    )
    {
        $this->wysiwygConfig = $wysiwygConfig;
        $this->systemStore = $systemStore;
        $this->_imageHelper = $imageHelper;
        $this->_importType = $importType;
        $this->_importBehaviour = $importBehaviour;
        $this->booleanOptions = $booleanOptions;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    protected function _prepareForm()
    {
        $form = $this->_formFactory->create(
            [
                'data' => [
                    'id' => 'edit_form',
                    'action' => '',
                    'method' => 'post',
                    'enctype' => 'multipart/form-data',
                ],
            ]
        );
        $form->setFieldNameSuffix('import');

        $fieldsets['base'] = $form->addFieldset(
            'base_fieldset',
            [
                'legend' => __('Import Settings')
            ]
        );

        $fieldsets['base']->addField('import_type', 'select', [
                'name' => 'import_type',
                'label' => __('Import Type'),
                'title' => __('Import Type'),
                'values' => $this->_importType->toOptionArray(),
                'required' => true,
                'onchange' => 'baniwalBlogImport.initImportFieldsSet();'
            ]
        );

        $fieldsetList = $this->_importType->toOptionArray();
        array_shift($fieldsetList);

        foreach ($fieldsetList as $item){

            $fieldsets[$item["value"]] = $form->addFieldset(
                $item["value"] . '_fieldset',
                [
                    'legend' => $item["label"]->getText(). __(' Import'),
                    'class' => 'no-display'
                ]
            );
            $fieldsets[$item["value"]]->addField(
                $item["value"].'_import_name',
                'hidden',
                [
                    'name' => 'import_name',
                    'value' => $item["label"]->getText()
                ]);

            $fieldsets[$item["value"]]->addField(
                $item["value"].'_db_name',
                'text',
                [
                    'name' => 'db_name',
                    'title' => __('Database Name'),
                    'label' => __('Database Name'),
                    'required' => true,
                    'class' => $item["value"],
                    'note' => __('Your SQL database name')
                ]
            );

            $fieldsets[$item["value"]]->addField(
                $item["value"].'_user_name',
                'text',
                [
                    'name' => 'user_name',
                    'title' => __('Database User Name'),
                    'label' => __('Database User Name'),
                    'required' => true,
                    'class' => $item["value"],
                    'note' => __('Your SQL database User name')
                ]
            );

            $fieldsets[$item["value"]]->addField(
                $item["value"].'_db_password',
                'text',
                [
                    'name' => 'db_password',
                    'title' => __('Database Password'),
                    'label' => __('Database Password'),
                    'class' => $item["value"],
                    'note' => __('Your SQL database Password')
                ]
            );

            $fieldsets[$item["value"]]->addField(
                $item["value"].'_db_host',
                'text',
                [
                    'name' => 'db_host',
                    'title' => __('Database Host'),
                    'label' => __('Database Host'),
                    'required' => true,
                    'class' => $item["value"],
                    'value' => 'localhost',
                    'note' => __('Your SQL database Hostname')
                ]
            );

            $fieldsets[$item["value"]]->addField(
                $item["value"].'_table_prefix',
                'text',
                [
                    'name' => 'table_prefix',
                    'title' => __('Table Prefix'),
                    'label' => __('Table Prefix'),
                    'class' => $item["value"],
                    'note' => __('Your table prefix name')
                ]
            );

            $fieldsets[$item["value"]]->addField(
                $item["value"].'_import_behaviour',
                'select',
                [
                    'name' => 'import_behaviour',
                    'label' => __('Import Behaviour'),
                    'title' => __('Import Behaviour'),
                    'values' => $this->_importBehaviour->toOptionArray(),
                    'note' => __('This action is applied to all data'),
                    'onchange' => 'baniwalBlogImport.initExpandBehaviour();'
                ]
            );

            $fieldsets[$item["value"]]->addField(
                $item["value"].'_import_behaviour_expand',
                'select',
                [
                    'name' => 'import_behaviour_expand',
                    'label' => __('Allow update content on existing URL-key'),
                    'title' => __('Allow update content on existing URL-key'),
                    'values' => $this->booleanOptions->toOptionArray(),
                    'note' => __('If <b>Yes</b>, content will be updated on posts with existing url-key. Select <b>No</b> to add a new post'),
                    'value' => 1,
                ]
            );

            $fieldsets[$item["value"]]->addField(
                $item["value"].'_import_image_path',
                'note',
                [
                    'name' => 'import_image_path',
                    'label' => __('Image Path Hint'),
                    'title' => __('Image Path Hint'),
                    'text' => '<div class="image-path"></div>',
                ]
            );
        }

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

}
