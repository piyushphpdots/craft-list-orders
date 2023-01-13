<?php
/**
 * List Orders plugin for Craft CMS 3.x
 *
 * List Orders
 *
 * @link      https://phpdots.com
 * @copyright Copyright (c) 2022 PHPDots Technologies
 */

namespace piyushphpdots\listorders\migrations;

use piyushphpdots\listorders\ListOrders;

use Craft;
use craft\config\DbConfig;
use craft\db\Migration;

/**
 * List Orders Install Migration
 *
 * If your plugin needs to create any custom database tables when it gets installed,
 * create a migrations/ folder within your plugin folder, and save an Install.php file
 * within it using the following template:
 *
 * If you need to perform any additional actions on install/uninstall, override the
 * safeUp() and safeDown() methods.
 *
 * @author    PHPDots Technologies
 * @package   ListOrders
 * @since     1.0.0
 */
class Install extends Migration
{
    // Public Properties
    // =========================================================================

    /**
     * @var string The database driver to use
     */
    public $driver;

    // Public Methods
    // =========================================================================

    /**
     * This method contains the logic to be executed when applying this migration.
     * This method differs from [[up()]] in that the DB logic implemented here will
     * be enclosed within a DB transaction.
     * Child classes may implement this method instead of [[up()]] if the DB logic
     * needs to be within a transaction.
     *
     * @return boolean return a false value to indicate the migration fails
     * and should not proceed further. All other return values mean the migration succeeds.
     */
    public function safeUp()
    {
        $this->driver = Craft::$app->getConfig()->getDb()->driver;
        if ($this->createTables()) {
            $this->createIndexes();
            $this->addForeignKeys();
            // Refresh the db schema caches
            Craft::$app->db->schema->refresh();
            $this->insertDefaultData();
        }

        return true;
    }

    /**
     * This method contains the logic to be executed when removing this migration.
     * This method differs from [[down()]] in that the DB logic implemented here will
     * be enclosed within a DB transaction.
     * Child classes may implement this method instead of [[down()]] if the DB logic
     * needs to be within a transaction.
     *
     * @return boolean return a false value to indicate the migration fails
     * and should not proceed further. All other return values mean the migration succeeds.
     */
    public function safeDown()
    {
        $this->driver = Craft::$app->getConfig()->getDb()->driver;
        $this->removeTables();

        return true;
    }

    // Protected Methods
    // =========================================================================

    /**
     * Creates the tables needed for the Records used by the plugin
     *
     * @return bool
     */
    protected function createTables()
    {
        $tablesCreated = false;

        // listorders_order table
        $tableSchema = Craft::$app->db->schema->getTableSchema('{{%listorders_order}}');
        if ($tableSchema === null) {
            $tablesCreated = true;
            $this->createTable(
                '{{%listorders_order}}',
                [
                    'id' => $this->primaryKey(),
                    'dateCreated' => $this->dateTime()->notNull(),
                    'dateUpdated' => $this->dateTime()->notNull(),
                    'uid' => $this->uid(),
                    'course_id' => $this->integer()->notNull(),
                    'course_name' => $this->string(255)->notNull(),
                    'course_date' => $this->date()->notNull(),
                    'price' => $this->float()->notNull()->defaultValue(0),
                    'stripe_id' => $this->string(255)->notNull(),
                    'first_name' => $this->string(255)->notNull(),
                    'last_name' => $this->string(255)->notNull(),
                    'house_number' => $this->string(255)->null(),
                    'workshop_title' => $this->string(255)->null(),
                    'customer_address' => $this->text(),
                    'postcode' => $this->string(255)->null(),
                    'email' => $this->string(255)->notNull(),
                    'phone' => $this->string(255)->notNull(),
                    'description' => $this->text(),
                    'card_name' => $this->string(255)->null(),
                    'status' => $this->string(255)->notNull(),
                ]
            );
        }

        $tableSchema = Craft::$app->db->schema->getTableSchema('{{%listorders_other_order}}');
        if ($tableSchema === null) {
            $tablesCreated = true;
            $this->createTable(
                '{{%listorders_other_order}}',
                [
                    'id' => $this->primaryKey(),
                    'dateCreated' => $this->dateTime()->notNull(),
                    'dateUpdated' => $this->dateTime()->notNull(),
                    'uid' => $this->uid(),
                    'course_id' => $this->integer()->notNull(),
                    'course_name' => $this->string(255)->notNull(),
                    'course_date' => $this->date()->notNull(),
                    'price' => $this->float()->null()->defaultValue(0),
                    'first_name' => $this->string(255)->notNull(),
                    'last_name' => $this->string(255)->notNull(),
                    'house_number' => $this->string(255)->null(),
                    'workshop_title' => $this->string(255)->null(),
                    'customer_address' => $this->text(),
                    'postcode' => $this->string(255)->null(),
                    'email' => $this->string(255)->notNull(),
                    'phone' => $this->string(255)->notNull(),
                    'description' => $this->text(),
                ]
            );
        }

        return $tablesCreated;
    }

    /**
     * Creates the indexes needed for the Records used by the plugin
     *
     * @return void
     */
    protected function createIndexes()
    {
    }

    /**
     * Creates the foreign keys needed for the Records used by the plugin
     *
     * @return void
     */
    protected function addForeignKeys()
    {
    }

    /**
     * Populates the DB with the default data.
     *
     * @return void
     */
    protected function insertDefaultData()
    {
    }

    /**
     * Removes the tables needed for the Records used by the plugin
     *
     * @return void
     */
    protected function removeTables()
    {
        // listorders_order table
        $this->dropTableIfExists('{{%listorders_order}}');
        $this->dropTableIfExists('{{%listorders_other_order}}');
    }
}
