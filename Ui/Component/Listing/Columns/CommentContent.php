<?php
namespace Baniwal\Blog\Ui\Component\Listing\Columns;

use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class CommentContent
 * @package Baniwal\Blog\Ui\Component\Listing\Columns
 */
class CommentContent extends Column
{
    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        $limitContent = 150;
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item[$this->getData('name')])) {
                    $content = $item['content'];
                    if (strlen($content) > $limitContent) {
                        $content = mb_substr($content, 0, $limitContent, 'UTF-8') . '.....';
                    }
                    $item[$this->getData('name')] = '<span>' . $content . '</span>';
                }
            }
        }
        return $dataSource;
    }
}
