<?php

namespace Baniwal\Blog\Ui\DataProvider\Blog\Form;

use Magento\Framework\UrlInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Baniwal\Blog\Model\ResourceModel\Category\CollectionFactory;

/**
 * DataProvider for new category form
 */
class NewCategoryDataProvider extends AbstractDataProvider
{
    protected $urlBuilder;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        UrlInterface $urlBuilder,
        array $meta = [],
        array $data = []
    )
    {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);

        $this->collection = $collectionFactory->create();
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        $this->data = array_replace_recursive(
            $this->data,
            [
                'config' => [
                    'data' => [
                        'is_active' => 1,
                        'include_in_menu' => 1,
                        'return_session_messages_only' => 1,
                        'use_config' => ['available_sort_by', 'default_sort_by']
                    ]
                ]
            ]
        );

        return $this->data;
    }

    /**
     * {@inheritdoc}
     */
    public function getMeta()
    {
        $this->meta = [
            'data' => [
                'children' => [
                    'parent' => [
                        'notice' => $this->getNotice(),
                    ]
                ]
            ]
        ];

        return parent::getMeta();
    }

    /**
     * Get notice message
     *
     * @return \Magento\Framework\Phrase
     */
    protected function getNotice()
    {
        return __(
            'If there are no custom parent categories, please use the default parent category. You can reassign the category at any time in <a href="%1" target="_blank">Blogs &gt; Categories</a>.',
            $this->urlBuilder->getUrl('baniwal_blog/category')
        );
    }
}
