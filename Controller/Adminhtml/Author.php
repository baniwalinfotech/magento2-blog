<?php

namespace Baniwal\Blog\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Baniwal\Blog\Model\AuthorFactory;

abstract class Author extends Action
{
    /** Authorization level of a basic admin session */
    const ADMIN_RESOURCE = 'Baniwal_Blog::author';

    public $coreRegistry;

    public $authorFactory;

    public function __construct(
        Context $context,
        Registry $coreRegistry,
        AuthorFactory $authorFactory
    )
    {
        $this->authorFactory = $authorFactory;
        $this->coreRegistry = $coreRegistry;

        parent::__construct($context);
    }

    public function initAuthor()
    {
        $user = $this->_auth->getUser();
        $userId = $user->getId();

        $author = $this->authorFactory->create()
            ->load($userId);

        if (!$author->getId()) {
            $author->setId($userId)
                ->setName($user->getName());
        }

        return $author;
    }
}
