<?php


/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace app\migrations;

use yii\db\Migration as DbMigration;

/**
 * Migration is the base class for representing a database migration.
 *
 * Migration is designed to be used together with the "yii migrate" command.
 *
 * Each child class of Migration represents an individual database migration which
 * is identified by the child class name.
 *
 * Within each migration, the [[up()]] method should be overridden to contain the logic
 * for "upgrading" the database; while the [[down()]] method for the "downgrading"
 * logic. The "yii migrate" command manages all available migrations in an application.
 *
 * If the database supports transactions, you may also override [[safeUp()]] and
 * [[safeDown()]] so that if anything wrong happens during the upgrading or downgrading,
 * the whole migration can be reverted in a whole.
 *
 * Note that some DB queries in some DBMS cannot be put into a transaction. For some examples,
 * please refer to [implicit commit](https://dev.mysql.com/doc/refman/5.7/en/implicit-commit.html). If this is the case,
 * you should still implement `up()` and `down()`, instead.
 *
 * Migration provides a set of convenient methods for manipulating database data and schema.
 * For example, the [[insert()]] method can be used to easily insert a row of data into
 * a database table; the [[createTable()]] method can be used to create a database table.
 * Compared with the same methods in [[Command]], these methods will display extra
 * information showing the method parameters and execution time, which may be useful when
 * applying migrations.
 *
 * For more details and usage information on Migration, see the [guide article on Migration](guide:db-migrations).
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Migration extends DbMigration
{
    /**
     * This method contains the logic to be executed when applying this migration.
     * Child classes may override this method to provide actual migration logic.
     * @return false|void|mixed return a false value to indicate the migration fails
     * and should not proceed further. All other return values mean the migration succeeds.
     */
    public function up()
    {
        $transaction = $this->db->beginTransaction();
        try {
            if ($this->safeUp() === false) {
                $transaction->rollBack();
                return false;
            }
            $transaction->commit();
        } catch (\Exception $e) {
            $this->printException($e);
            $transaction->rollBack();
            return false;
        } catch (\Throwable $e) {
            $this->printException($e);
            $transaction->rollBack();
            return false;
        }

        return null;
    }

    /**
     * This method contains the logic to be executed when removing this migration.
     * The default implementation throws an exception indicating the migration cannot be removed.
     * Child classes may override this method if the corresponding migrations can be removed.
     * @return false|void|mixed return a false value to indicate the migration fails
     * and should not proceed further. All other return values mean the migration succeeds.
     */
    public function down()
    {
        $transaction = $this->db->beginTransaction();
        try {
            if ($this->safeDown() === false) {
                $transaction->rollBack();
                return false;
            }
            $transaction->commit();
        } catch (\Exception $e) {
            $this->printException($e);
            $transaction->rollBack();
            return false;
        } catch (\Throwable $e) {
            $this->printException($e);
            $transaction->rollBack();
            return false;
        }

        return null;
    }

    /**
     *  function up() dan down() bisa dihapus
     *  setelah PR ini dimerge -> https://github.com/yiisoft/yii2/pull/20163
     * 
     * @param \Throwable $e
     */
    protected function printException($e)
    {
        echo 'Exception: ' . $e->getMessage() ."\n";
        //  . "\n    at (" . $e->getFile() . ':' . $e->getLine() . ")\n";
        // echo $e->getTraceAsString() . "\n";
    }
}