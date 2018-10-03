<?php

namespace Baniwal\Blog\Block\Adminhtml\Post\Edit\Tab\Renderer;

use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Data\Form\Element\CollectionFactory;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\Data\Form\Element\Multiselect;
use Magento\Framework\Escaper;
use Magento\Framework\UrlInterface;
use Baniwal\Blog\Model\ResourceModel\Tag\CollectionFactory as BlogTagCollectionFactory;

class Tag extends Multiselect
{
    public $collectionFactory;

    public $authorization;

    protected $_urlBuilder;

    public function __construct(
        Factory $factoryElement,
        CollectionFactory $factoryCollection,
        Escaper $escaper,
        BlogTagCollectionFactory $collectionFactory,
        AuthorizationInterface $authorization,
        UrlInterface $urlBuilder,
        array $data = []
    )
    {
        $this->collectionFactory = $collectionFactory;
        $this->authorization = $authorization;
        $this->_urlBuilder = $urlBuilder;

        parent::__construct($factoryElement, $factoryCollection, $escaper, $data);
    }

    public function getElementHtml()
    {
        $html = '<div class="admin__field-control admin__control-grouped">';
        $html .= '<div id="blog-tag-select" class="admin__field" data-bind="scope:\'blogTag\'" data-index="index">';
        $html .= '<!-- ko foreach: elems() -->';
        $html .= '<input name="post[tags_ids]" data-bind="value: value" style="display: none"/>';
        $html .= '<!-- ko template: elementTmpl --><!-- /ko -->';
        $html .= '<!-- /ko -->';
        $html .= '</div>';

        $html .= '<div class="admin__field admin__field-group-additional admin__field-small" data-bind="scope:\'create_tag_button\'">';
        $html .= '<div class="admin__field-control">';
        $html .= '<!-- ko template: elementTmpl --><!-- /ko -->';
        $html .= '</div></div></div>';

        $html .= '<!-- ko scope: \'create_tag_modal\' --><!-- ko template: getTemplate() --><!-- /ko --><!-- /ko -->';

        $html .= $this->getAfterElementHtml();

        return $html;
    }

    public function getNoDisplay()
    {
        $isNotAllowed = !$this->authorization->isAllowed('Baniwal_Blog::tag');

        return $this->getData('no_display') || $isNotAllowed;
    }

    public function getTagsCollection()
    {
        $collection = $this->collectionFactory->create();
        $tagById = [];
        foreach ($collection as $tag) {
            $tagById[$tag->getId()]['value'] = $tag->getId();
            $tagById[$tag->getId()]['is_active'] = 1;
            $tagById[$tag->getId()]['label'] = $tag->getName();

        }

        return $tagById;
    }

    /**
     * Get values for select
     *
     * @return array
     */
    public function getValues()
    {
        $values = $this->getValue();

        if (!is_array($values)) {
            $values = explode(',', $values);
        }

        if (!sizeof($values)) {
            return [];
        }

        $collection = $this->collectionFactory->create()
            ->addIdFilter($values);

        $options = [];
        foreach ($collection as $tag) {
            $options[] = $tag->getId();
        }

        return $options;
    }

    public function getAfterElementHtml()
    {
        $html = '<script type="text/x-magento-init">
            {
                "*": {
                    "Magento_Ui/js/core/app": {
                        "components": {
                            "blogTag": {
                                "component": "uiComponent",
                                "children": {
                                    "blog_select_tag": {
                                        "component": "Baniwal_Blog/js/components/new-category",
                                        "config": {
                                            "filterOptions": true,
                                            "disableLabel": true,
                                            "chipsEnabled": true,
                                            "levelsVisibility": "1",
                                            "elementTmpl": "ui/grid/filters/elements/ui-select",
                                            "options": ' . json_encode($this->getTagsCollection()) . ',
                                            "value": ' . json_encode($this->getValues()) . ',
                                            "listens": {
                                                "index=create_tag:responseData": "setParsed",
                                                "newOption": "toggleOptionSelected"
                                            },
                                            "config": {
                                                "dataScope": "blog_select_tag",
                                                "sortOrder": 10
                                            }
                                        }
                                    }
                                }
                            },
                            "create_tag_button": {
                                "title": "' . __('New Tag') . '",
                                "formElement": "container",
                                "additionalClasses": "admin__field-small",
                                "componentType": "container",
                                "component": "Magento_Ui/js/form/components/button",
                                "template": "ui/form/components/button/container",
                                "actions": [
                                    {
                                        "targetName": "create_tag_modal",
                                        "actionName": "toggleModal"
                                    },
                                    {
                                        "targetName": "create_tag_modal.create_tag",
                                        "actionName": "render"
                                    },
                                    {
                                        "targetName": "create_tag_modal.create_tag",
                                        "actionName": "resetForm"
                                    }
                                ],
                                "additionalForGroup": true,
                                "provider": false,
                                "source": "product_details",
                                "displayArea": "insideGroup"
                            },
                            "create_tag_modal": {
                                "config": {
                                    "isTemplate": false,
                                    "componentType": "container",
                                    "component": "Magento_Ui/js/modal/modal-component",
                                    "options": {
                                        "title": "' . __('New Tag') . '",
                                        "type": "slide"
                                    },
                                    "imports": {
                                        "state": "!index=create_tag:responseStatus"
                                    }
                                },
                                "children": {
                                    "create_tag": {
                                        "label": "",
                                        "componentType": "container",
                                        "component": "Magento_Ui/js/form/components/insert-form",
                                        "dataScope": "",
                                        "update_url": "' . $this->_urlBuilder->getUrl('mui/index/render') . '",
                                        "render_url": "' . $this->_urlBuilder->getUrl('mui/index/render_handle', ['handle' => 'baniwal_blog_tag_create', 'buttons' => 1]) . '",
                                        "autoRender": false,
                                        "ns": "blog_new_tag_form",
                                        "externalProvider": "blog_new_tag_form.new_tag_form_data_source",
                                        "toolbarContainer": "${ $.parentName }",
                                        "formSubmitType": "ajax"
                                    }
                                }
                            }
                        }
                    }
                }
            }
        </script>';

        return $html;
    }
}
