<?php

namespace Baniwal\Blog\Controller;

use Magento\Framework\App\ActionFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\RouterInterface;
use Magento\Framework\Url;
use Baniwal\Blog\Helper\Data;

class Router implements RouterInterface
{
    const URL_SUFFIX_RSS_XML = ".xml";

    public $actionFactory;

    public $helper;

    protected $_request;

    public function __construct(
        ActionFactory $actionFactory,
        Data $helper
    )
    {
        $this->actionFactory = $actionFactory;
        $this->helper = $helper;
    }

    public function _forward($controller, $action, $params = [])
    {
        $this->_request->setControllerName($controller)
            ->setActionName($action)
            ->setPathInfo('/baniwalblog/' . $controller . '/' . $action);

        foreach ($params as $key => $value) {
            $this->_request->setParam($key, $value);
        }

        return $this->actionFactory->create('Magento\Framework\App\Action\Forward');
    }

    public function match(RequestInterface $request)
    {
        if (!$this->helper->isEnabled()) {
            return null;
        }

        $rssAction = "rss.xml";
        $identifier = trim($request->getPathInfo(), '/');
        $urlSuffix = $this->helper->getUrlSuffix();

        if ($length = strlen($urlSuffix)) {
            if (substr($identifier, -$length) == $urlSuffix && !$this->isRss($identifier)) {
                $identifier = substr($identifier, 0, strlen($identifier) - $length);
            } else {
                $identifier = $this->checkRssIdentifier($identifier);
            }
        } else {
            if (strpos($identifier, $rssAction) !== false) {
                $identifier = $this->checkRssIdentifier($identifier);
            }
        }

        $routePath = explode('/', $identifier);
        $routeSize = sizeof($routePath);
        if (!$routeSize || ($routeSize > 3) || (array_shift($routePath) != $this->helper->getRoute())) {
            return null;
        }

        $request->setModuleName('baniwalblog')
            ->setAlias(Url::REWRITE_REQUEST_PATH_ALIAS, $identifier . $urlSuffix);
        $controller = array_shift($routePath);
        if (!$controller) {
            $request->setControllerName('post')
                ->setActionName('index')
                ->setPathInfo('/baniwalblog/post/index');

            return $this->actionFactory->create('Magento\Framework\App\Action\Forward');
        }

        $action = array_shift($routePath) ?: 'index';

        switch ($controller) {
            case 'post':
                if (!in_array($action, ['index', 'rss'])) {
                    $post = $this->helper->getObjectByParam($action, 'url_key');
                    $request->setParam('id', $post->getId());
                    $action = 'view';
                }
                break;
            case 'category':
                if (!in_array($action, ['index', 'rss'])) {
                    $category = $this->helper->getObjectByParam($action, 'url_key', Data::TYPE_CATEGORY);
                    $request->setParam('id', $category->getId());
                    $action = 'view';
                }
                break;
            case 'tag':
                $tag = $this->helper->getObjectByParam($action, 'url_key', Data::TYPE_TAG);
                $request->setParam('id', $tag->getId());
                $action = 'view';
                break;
            case 'topic':
                $topic = $this->helper->getObjectByParam($action, 'url_key', Data::TYPE_TOPIC);
                $request->setParam('id', $topic->getId());
                $action = 'view';
                break;
            case 'sitemap':
                $action = 'index';
                break;
            case 'author':
                $author = $this->helper->getObjectByParam($action, 'url_key', Data::TYPE_AUTHOR);
                $request->setParam('id', $author->getId());
                $action = 'view';
                break;
            case 'month':
                $request->setParam('month_key', $action);
                $action = 'view';
                break;
            default:
                $post = $this->helper->getObjectByParam($controller, 'url_key');
                $request->setParam('id', $post->getId());
                $controller = 'post';
                $action = 'view';
        }

        $request->setControllerName($controller)
            ->setActionName($action)
            ->setPathInfo('/baniwalblog/' . $controller . '/' . $action);

        return $this->actionFactory->create('Magento\Framework\App\Action\Forward');
    }

    public function isRss($identifier)
    {
        $routePath = explode('/', $identifier);
        $routePath = array_pop($routePath);
        $routePath = explode('.', $routePath);
        $action = array_shift($routePath);

        return ($action == "rss");
    }

    public function checkRssIdentifier($identifier)
    {
        $length = strlen(self::URL_SUFFIX_RSS_XML);
        if (substr($identifier, -$length) == self::URL_SUFFIX_RSS_XML && $this->isRss($identifier)) {
            $identifier = substr($identifier, 0, strlen($identifier) - $length);
            return $identifier;
        } else {
            return null;
        }
    }
}
