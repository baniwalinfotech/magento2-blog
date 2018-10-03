<?php

namespace Baniwal\Blog\Plugin;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\Tree\Node;
use Magento\Framework\Data\TreeFactory;
use Baniwal\Blog\Helper\Data;

class Topmenu
{
    protected $helper;

    protected $treeFactory;

    protected $request;

    public function __construct(
        Data $helper,
        TreeFactory $treeFactory,
        RequestInterface $request
    )
    {
        $this->helper = $helper;
        $this->treeFactory = $treeFactory;
        $this->request = $request;
    }

    public function beforeGetHtml(
        \Magento\Theme\Block\Html\Topmenu $subject,
        $outermostClass = '',
        $childrenWrapClass = '',
        $limit = 0
    )
    {
        if ($this->helper->isEnabled() && $this->helper->getBlogConfig('general/toplinks')) {
            $subject->getMenu()
                ->addChild(
                    new Node(
                        $this->getMenuAsArray(),
                        'id',
                        $this->treeFactory->create()
                    )
                );
        }

        return [$outermostClass, $childrenWrapClass, $limit];
    }

    private function getMenuAsArray()
    {
        $identifier = trim($this->request->getPathInfo(), '/');
        $routePath = explode('/', $identifier);
        $routeSize = sizeof($routePath);

        return [
            'name' => $this->helper->getBlogConfig('general/name') ?: __('Blog'),
            'id' => 'baniwalblog-node',
            'url' => $this->helper->getBlogUrl(''),
            'has_active' => ($identifier == 'baniwalblog/post/index'),
            'is_active' => ('baniwalblog' == array_shift($routePath)) && ($routeSize == 3)
        ];
    }
}
