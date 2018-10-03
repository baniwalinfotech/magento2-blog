<?php

namespace Baniwal\Blog\Setup;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

/**
 * @codeCoverageIgnore
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        $connection = $installer->getConnection();

        if (version_compare($context->getVersion(), '1.1.1', '<')) {
            if ($installer->tableExists('baniwal_blog_tag')) {
                $columns = [
                    'meta_title' => [
                        'type' => Table::TYPE_TEXT,
                        'length' => 255,
                        'comment' => 'Post Meta Title',
                    ],
                    'meta_description' => [
                        'type' => Table::TYPE_TEXT,
                        'length' => '64k',
                        'comment' => 'Post Meta Description',
                    ],
                    'meta_keywords' => [
                        'type' => Table::TYPE_TEXT,
                        'length' => '64k',
                        'comment' => 'Post Meta Keywords',
                    ],
                    'meta_robots' => [
                        'type' => Table::TYPE_INTEGER,
                        'length' => '64k',
                        'comment' => 'Post Meta Robots',
                    ]
                ];


                $tagTable = $installer->getTable('baniwal_blog_tag');
                foreach ($columns as $name => $definition) {
                    $connection->addColumn($tagTable, $name, $definition);
                }
            }

            if (!$installer->tableExists('baniwal_blog_post_traffic')) {
                $table = $installer->getConnection()
                    ->newTable($installer->getTable('baniwal_blog_post_traffic'))
                    ->addColumn('traffic_id', Table::TYPE_INTEGER, null, [
                        'identity' => true,
                        'nullable' => false,
                        'primary' => true,
                        'unsigned' => true,
                    ], 'Traffic ID')
                    ->addColumn('post_id', Table::TYPE_INTEGER, null, [
                        'unsigned' => true,
                        'nullable' => false
                    ], 'Post ID')
                    ->addColumn('numbers_view', Table::TYPE_TEXT, 255, [], 'Numbers View')
                    ->addIndex($installer->getIdxName('baniwal_blog_post_traffic', ['post_id']), ['post_id'])
                    ->addIndex($installer->getIdxName('baniwal_blog_post_traffic', ['traffic_id']), ['traffic_id'])
                    ->addForeignKey(
                        $installer->getFkName('baniwal_blog_post_traffic', 'post_id', 'baniwal_blog_post', 'post_id'),
                        'post_id',
                        $installer->getTable('baniwal_blog_post'),
                        'post_id',
                        Table::ACTION_CASCADE
                    )
                    ->addIndex(
                        $installer->getIdxName('baniwal_blog_post_traffic', ['post_id', 'traffic_id'], AdapterInterface::INDEX_TYPE_UNIQUE),
                        ['post_id', 'traffic_id'],
                        ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
                    )
                    ->setComment('Traffic Post Table');

                $installer->getConnection()->createTable($table);
            }
            if (!$installer->tableExists('baniwal_blog_author')) {
                $table = $installer->getConnection()
                    ->newTable($installer->getTable('baniwal_blog_author'))
                    ->addColumn('user_id', Table::TYPE_INTEGER, null, [
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true,
                    ], 'User ID')
                    ->addColumn('name', Table::TYPE_TEXT, 255, [], 'Display Name')
                    ->addColumn('url_key', Table::TYPE_TEXT, 255, [], 'Author URL Key')
                    ->addColumn('updated_at', Table::TYPE_TIMESTAMP, null, [], 'Author Updated At')
                    ->addColumn('created_at', Table::TYPE_TIMESTAMP, null, ['default' => Table::TIMESTAMP_INIT], 'Author Created At')
                    ->addForeignKey(
                        $installer->getFkName('baniwal_blog_author', 'user_id', 'admin_user', 'user_id'),
                        'user_id',
                        $installer->getTable('admin_user'),
                        'user_id',
                        Table::ACTION_CASCADE
                    )
                    ->addIndex(
                        $installer->getIdxName('baniwal_blog_author', ['user_id'], AdapterInterface::INDEX_TYPE_UNIQUE),
                        ['user_id'],
                        ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
                    )
                    ->setComment('Author Table');

                $installer->getConnection()->createTable($table);
            }

            if ($installer->tableExists('baniwal_blog_topic')) {
                $columns = [
                    'author_id' => [
                        'type' => Table::TYPE_INTEGER,
                        'comment' => 'Author ID',
                        'unsigned' => true,
                    ],
                    'modifier_id' => [
                        'type' => Table::TYPE_INTEGER,
                        'comment' => 'Modifier ID',
                        'unsigned' => true,
                    ],
                ];

                $table = $installer->getTable('baniwal_blog_post');
                foreach ($columns as $name => $definition) {
                    $connection->addColumn($table, $name, $definition);
                }
            }
        }
        if (version_compare($context->getVersion(), '1.1.2', '<')) {
            if ($installer->tableExists('baniwal_blog_post')) {
                $connection->modifyColumn($installer->getTable('baniwal_blog_post'), 'meta_robots', ['type' => Table::TYPE_TEXT]);
            }
            if ($installer->tableExists('baniwal_blog_tag')) {
                $connection->modifyColumn($installer->getTable('baniwal_blog_tag'), 'meta_robots', ['type' => Table::TYPE_TEXT]);
            }
            if ($installer->tableExists('baniwal_blog_category')) {
                $connection->modifyColumn($installer->getTable('baniwal_blog_category'), 'meta_robots', ['type' => Table::TYPE_TEXT]);
            }
            if ($installer->tableExists('baniwal_blog_topic')) {
                $connection->modifyColumn($installer->getTable('baniwal_blog_topic'), 'meta_robots', ['type' => Table::TYPE_TEXT]);
            }

            if (!$installer->tableExists('baniwal_blog_comment')) {
                $table = $installer->getConnection()
                    ->newTable($installer->getTable('baniwal_blog_comment'))
                    ->addColumn('comment_id', Table::TYPE_INTEGER, null, [
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true,
                    ], 'Comment ID')
                    ->addColumn('post_id', Table::TYPE_INTEGER, null, ['unsigned' => true, 'nullable' => false,], 'Post ID')
                    ->addColumn('entity_id', Table::TYPE_INTEGER, null, ['unsigned' => true, 'nullable' => false,], 'User Comment ID')
                    ->addColumn('has_reply', Table::TYPE_SMALLINT, 2, ['unsigned' => true, 'nullable' => false, 'default' => 0], 'Comment has reply')
                    ->addColumn('is_reply', Table::TYPE_SMALLINT, 2, ['unsigned' => true, 'nullable' => false, 'default' => 0], 'Is reply comment')
                    ->addColumn('reply_id', Table::TYPE_INTEGER, null, ['unsigned' => true, 'nullable' => true, 'default' => 0], 'Reply ID')
                    ->addColumn('content', Table::TYPE_TEXT, 255, [], 'Comment content')
                    ->addColumn('created_at', Table::TYPE_TEXT, null, [], 'Comment Created At')
                    ->addColumn('status', Table::TYPE_SMALLINT, 3, ['unsigned' => true, 'nullable' => false, 'default' => 3], 'Status')
                    ->addColumn('store_ids', Table::TYPE_TEXT, null, ['nullable' => false, 'unsigned' => true,], 'Store Id')
                    ->addIndex($installer->getIdxName('baniwal_blog_comment', ['comment_id']), ['comment_id'])
                    ->addIndex($installer->getIdxName('baniwal_blog_comment', ['entity_id']), ['entity_id'])
                    ->addForeignKey(
                        $installer->getFkName('baniwal_blog_comment', 'entity_id', 'customer_entity', 'entity_id'),
                        'entity_id',
                        $installer->getTable('customer_entity'),
                        'entity_id',
                        Table::ACTION_CASCADE
                    )->addForeignKey(
                        $installer->getFkName('baniwal_blog_comment', 'post_id', 'baniwal_blog_post', 'post_id'),
                        'post_id',
                        $installer->getTable('baniwal_blog_post'),
                        'post_id',
                        Table::ACTION_CASCADE
                    );

                $installer->getConnection()->createTable($table);
            }
            if (!$installer->tableExists('baniwal_blog_comment_like')) {
                $table = $installer->getConnection()
                    ->newTable($installer->getTable('baniwal_blog_comment_like'))
                    ->addColumn('like_id', Table::TYPE_INTEGER, null, [
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true,
                    ], 'Like ID')
                    ->addColumn('comment_id', Table::TYPE_INTEGER, null, ['unsigned' => true, 'nullable' => false,], 'Comment ID')
                    ->addColumn('entity_id', Table::TYPE_INTEGER, null, ['unsigned' => true, 'nullable' => false,], 'User Like ID')
                    ->addIndex($installer->getIdxName('baniwal_blog_comment_like', ['like_id']), ['like_id'])
                    ->addForeignKey(
                        $installer->getFkName('baniwal_blog_comment_like', 'comment_id', 'baniwal_blog_comment', 'comment_id'),
                        'comment_id',
                        $installer->getTable('baniwal_blog_comment'),
                        'comment_id',
                        Table::ACTION_CASCADE
                    )->addForeignKey(
                        $installer->getFkName('baniwal_blog_comment_like', 'entity_id', 'customer_entity', 'entity_id'),
                        'entity_id',
                        $installer->getTable('customer_entity'),
                        'entity_id',
                        Table::ACTION_CASCADE
                    );

                $installer->getConnection()->createTable($table);
            }
        }

        if (version_compare($context->getVersion(), '1.1.3', '<')) {
            if ($installer->tableExists('baniwal_blog_author')) {
                $columns = [
                    'image' => [
                        'type' => Table::TYPE_TEXT,
                        'length' => 255,
                        'comment' => 'Author Image',
                        'unsigned' => true,
                    ],
                    'short_description' => [
                        'type' => Table::TYPE_TEXT,
                        'length' => '64k',
                        'comment' => 'Author Short Description',
                        'unsigned' => true,
                    ],
                    'facebook_link' => [
                        'type' => Table::TYPE_TEXT,
                        'length' => 255,
                        'comment' => 'Facebook Link',
                        'unsigned' => true,
                    ],
                    'twitter_link' => [
                        'type' => Table::TYPE_TEXT,
                        'length' => 255,
                        'comment' => 'Twitter Link',
                        'unsigned' => true,
                    ],
                ];

                $table = $installer->getTable('baniwal_blog_author');
                foreach ($columns as $name => $definition) {
                    $connection->addColumn($table, $name, $definition);
                }
            }
        }

        if (version_compare($context->getVersion(), '1.1.4', '<')) {
            if (!$installer->tableExists('baniwal_blog_post_product')) {
                $table = $installer->getConnection()
                    ->newTable($installer->getTable('baniwal_blog_post_product'))
                    ->addColumn('post_id', Table::TYPE_INTEGER, null, [
                        'unsigned' => true,
                        'primary' => true,
                        'nullable' => false
                    ], 'Post ID')
                    ->addColumn('entity_id', Table::TYPE_INTEGER, null, [
                        'unsigned' => true,
                        'primary' => true,
                        'nullable' => false
                    ], 'Entity ID')
                    ->addColumn('position', Table::TYPE_INTEGER, null, ['nullable' => false, 'default' => '0'], 'Position')
                    ->addIndex($installer->getIdxName('baniwal_blog_post_product', ['post_id']), ['post_id'])
                    ->addIndex($installer->getIdxName('baniwal_blog_post_product', ['entity_id']), ['entity_id'])
                    ->addForeignKey(
                        $installer->getFkName('baniwal_blog_post_product', 'post_id', 'baniwal_blog_post', 'post_id'),
                        'post_id',
                        $installer->getTable('baniwal_blog_post'),
                        'post_id',
                        Table::ACTION_CASCADE
                    )
                    ->addForeignKey(
                        $installer->getFkName('baniwal_blog_post_product', 'entity_id', 'catalog_product_entity', 'entity_id'),
                        'entity_id',
                        $installer->getTable('catalog_product_entity'),
                        'entity_id',
                        Table::ACTION_CASCADE
                    )
                    ->addIndex(
                        $installer->getIdxName('baniwal_blog_post_product', ['post_id', 'entity_id'], AdapterInterface::INDEX_TYPE_UNIQUE),
                        ['post_id', 'entity_id'],
                        ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
                    )
                    ->setComment('Post To Product Link Table');

                $installer->getConnection()->createTable($table);
            }
        }

        if (version_compare($context->getVersion(), '2.4.2', '<')) {
            if ($installer->tableExists('baniwal_blog_post')) {
                $connection->addColumn($installer->getTable('baniwal_blog_post'), 'publish_date', [
                    'type' => Table::TYPE_TIMESTAMP, null,
                    'comment' => 'Post Publish Date',
                ]);
            }
        }

        if (version_compare($context->getVersion(), '2.4.3', '<')) {
            if ($installer->tableExists('baniwal_blog_post')) {
                $connection->modifyColumn($installer->getTable('baniwal_blog_post'), 'created_at', ['type' => Table::TYPE_DATETIME]);
            }
            if ($installer->tableExists('baniwal_blog_post')) {
                $connection->modifyColumn($installer->getTable('baniwal_blog_post'), 'updated_at', ['type' => Table::TYPE_DATETIME]);
            }
            if ($installer->tableExists('baniwal_blog_post')) {
                $connection->modifyColumn($installer->getTable('baniwal_blog_post'), 'publish_date', ['type' => Table::TYPE_DATETIME]);
            }
            if ($installer->tableExists('baniwal_blog_tag')) {
                $connection->modifyColumn($installer->getTable('baniwal_blog_tag'), 'created_at', ['type' => Table::TYPE_DATETIME]);
            }
            if ($installer->tableExists('baniwal_blog_tag')) {
                $connection->modifyColumn($installer->getTable('baniwal_blog_tag'), 'updated_at', ['type' => Table::TYPE_DATETIME]);
            }
            if ($installer->tableExists('baniwal_blog_category')) {
                $connection->modifyColumn($installer->getTable('baniwal_blog_category'), 'created_at', ['type' => Table::TYPE_DATETIME]);
            }
            if ($installer->tableExists('baniwal_blog_category')) {
                $connection->modifyColumn($installer->getTable('baniwal_blog_category'), 'updated_at', ['type' => Table::TYPE_DATETIME]);
            }
            if ($installer->tableExists('baniwal_blog_topic')) {
                $connection->modifyColumn($installer->getTable('baniwal_blog_topic'), 'created_at', ['type' => Table::TYPE_DATETIME]);
            }
            if ($installer->tableExists('baniwal_blog_topic')) {
                $connection->modifyColumn($installer->getTable('baniwal_blog_topic'), 'updated_at', ['type' => Table::TYPE_DATETIME]);
            }
        }

        if (version_compare($context->getVersion(), '2.4.4', '<')) {
            if ($installer->tableExists('baniwal_blog_comment')) {
                $connection->modifyColumn($installer->getTable('baniwal_blog_comment'), 'content', ['type' => Table::TYPE_TEXT]);
            }
            if ($installer->tableExists('baniwal_blog_comment')) {
                $connection->modifyColumn($installer->getTable('baniwal_blog_comment'), 'created_at', ['type' => Table::TYPE_DATETIME]);
            }

            if ($installer->tableExists('baniwal_blog_comment')) {
                if (!$connection->tableColumnExists($installer->getTable('baniwal_blog_comment'), 'status')) {
                    $connection->addColumn($installer->getTable('baniwal_blog_comment'), 'status', [
                        'type' => Table::TYPE_INTEGER, 3, ['unsigned' => true, 'nullable' => false, 'default' => 3],
                        'comment' => 'status',
                    ]);
                }
                if (!$connection->tableColumnExists($installer->getTable('baniwal_blog_comment'), 'store_ids')) {
                    $connection->addColumn($installer->getTable('baniwal_blog_comment'), 'store_ids', [
                        'type' => Table::TYPE_TEXT, null, ['nullable' => false, 'unsigned' => true,],
                        'comment' => 'Store Id',
                    ]);
                }
            }
        }

        if (version_compare($context->getVersion(), '2.4.5', '<')) {
            if ($installer->tableExists('baniwal_blog_post_traffic')) {
                $connection->modifyColumn($installer->getTable('baniwal_blog_post_traffic'), 'numbers_view', ['type' => Table::TYPE_INTEGER]);
            }
        }

        if (version_compare($context->getVersion(), '2.4.6', '<')) {
            if ($installer->tableExists('baniwal_blog_comment')) {
                $connection->dropForeignKey(
                    $installer->getTable('baniwal_blog_comment'),
                    $installer->getFkName('baniwal_blog_comment', 'entity_id', 'customer_entity', 'entity_id')
                );
            }
        }

        if (version_compare($context->getVersion(), '2.4.7', '<')) {
            if ($installer->tableExists('baniwal_blog_comment')) {
                if (!$connection->tableColumnExists($installer->getTable('baniwal_blog_comment'), 'user_name')) {
                    $connection->addColumn($installer->getTable('baniwal_blog_comment'), 'user_name', [
                        'type' => Table::TYPE_TEXT, null, ['unsigned' => true, 'nullable' => true],
                        'comment' => 'User Name',
                    ]);
                }
                if (!$connection->tableColumnExists($installer->getTable('baniwal_blog_comment'), 'user_email')) {
                    $connection->addColumn($installer->getTable('baniwal_blog_comment'), 'user_email', [
                        'type' => Table::TYPE_TEXT, null, ['unsigned' => true, 'nullable' => true],
                        'comment' => 'User Email',
                    ]);
                }
            }
        }

        if (version_compare($context->getVersion(), '2.4.8', '<')) {
            if ($installer->tableExists('baniwal_blog_post')) {
                if (!$connection->tableColumnExists($installer->getTable('baniwal_blog_post'), 'import_source')) {
                    $connection->addColumn($installer->getTable('baniwal_blog_post'), 'import_source', [
                        'type' => Table::TYPE_TEXT, null, ['unsigned' => true, 'nullable' => true],
                        'comment' => 'Import Source',
                    ]);
                }
            }

            if ($installer->tableExists('baniwal_blog_tag')) {
                if (!$connection->tableColumnExists($installer->getTable('baniwal_blog_tag'), 'import_source')) {
                    $connection->addColumn($installer->getTable('baniwal_blog_tag'), 'import_source', [
                        'type' => Table::TYPE_TEXT, null, ['unsigned' => true, 'nullable' => true],
                        'comment' => 'Import Source',
                    ]);
                }
            }

            if ($installer->tableExists('baniwal_blog_category')) {
                if (!$connection->tableColumnExists($installer->getTable('baniwal_blog_category'), 'import_source')) {
                    $connection->addColumn($installer->getTable('baniwal_blog_category'), 'import_source', [
                        'type' => Table::TYPE_TEXT, null, ['unsigned' => true, 'nullable' => true],
                        'comment' => 'Import Source',
                    ]);
                }
            }

            if ($installer->tableExists('baniwal_blog_comment')) {
                if (!$connection->tableColumnExists($installer->getTable('baniwal_blog_comment'), 'import_source')) {
                    $connection->addColumn($installer->getTable('baniwal_blog_comment'), 'import_source', [
                        'type' => Table::TYPE_TEXT, null, ['unsigned' => true, 'nullable' => true],
                        'comment' => 'Import Source',
                    ]);
                }
            }

            if ($installer->tableExists('baniwal_blog_topic')) {
                if (!$connection->tableColumnExists($installer->getTable('baniwal_blog_topic'), 'import_source')) {
                    $connection->addColumn($installer->getTable('baniwal_blog_topic'), 'import_source', [
                        'type' => Table::TYPE_TEXT, null, ['unsigned' => true, 'nullable' => true],
                        'comment' => 'Import Source',
                    ]);
                }
            }
        }

        if (version_compare($context->getVersion(), '2.4.9', '<')) {
            if ($installer->tableExists('baniwal_blog_post')) {
                if ($connection->tableColumnExists($installer->getTable('baniwal_blog_post'), 'created_at')) {
                    $connection->modifyColumn($installer->getTable('baniwal_blog_post'), 'created_at', ['type' => Table::TYPE_TIMESTAMP]);
                }
                if ($connection->tableColumnExists($installer->getTable('baniwal_blog_post'), 'updated_at')) {
                    $connection->modifyColumn($installer->getTable('baniwal_blog_post'), 'updated_at', ['type' => Table::TYPE_TIMESTAMP]);
                }
                if ($connection->tableColumnExists($installer->getTable('baniwal_blog_post'), 'publish_date')) {
                    $connection->modifyColumn($installer->getTable('baniwal_blog_post'), 'publish_date', ['type' => Table::TYPE_TIMESTAMP]);
                }
            }
            if ($installer->tableExists('baniwal_blog_category')) {
                if ($connection->tableColumnExists($installer->getTable('baniwal_blog_category'), 'created_at')) {
                    $connection->modifyColumn($installer->getTable('baniwal_blog_category'), 'created_at', ['type' => Table::TYPE_TIMESTAMP]);
                }
                if ($connection->tableColumnExists($installer->getTable('baniwal_blog_category'), 'updated_at')) {
                    $connection->modifyColumn($installer->getTable('baniwal_blog_category'), 'updated_at', ['type' => Table::TYPE_TIMESTAMP]);
                }
            }
            if ($installer->tableExists('baniwal_blog_tag')) {
                if ($connection->tableColumnExists($installer->getTable('baniwal_blog_tag'), 'created_at')) {
                    $connection->modifyColumn($installer->getTable('baniwal_blog_tag'), 'created_at', ['type' => Table::TYPE_TIMESTAMP]);
                }
                if ($connection->tableColumnExists($installer->getTable('baniwal_blog_tag'), 'updated_at')) {
                    $connection->modifyColumn($installer->getTable('baniwal_blog_tag'), 'updated_at', ['type' => Table::TYPE_TIMESTAMP]);
                }
            }
            if ($installer->tableExists('baniwal_blog_topic')) {
                if ($connection->tableColumnExists($installer->getTable('baniwal_blog_topic'), 'created_at')) {
                    $connection->modifyColumn($installer->getTable('baniwal_blog_topic'), 'created_at', ['type' => Table::TYPE_TIMESTAMP]);
                }
                if ($connection->tableColumnExists($installer->getTable('baniwal_blog_topic'), 'updated_at')) {
                    $connection->modifyColumn($installer->getTable('baniwal_blog_topic'), 'updated_at', ['type' => Table::TYPE_TIMESTAMP]);
                }
            }
            if ($installer->tableExists('baniwal_blog_comment')) {
                if ($connection->tableColumnExists($installer->getTable('baniwal_blog_comment'), 'created_at')) {
                    $connection->modifyColumn($installer->getTable('baniwal_blog_comment'), 'created_at', ['type' => Table::TYPE_TIMESTAMP]);
                }
            }
        }

        $installer->endSetup();
    }
}
