<?php
namespace OCA\GnuLinux\Migration;

use OCP\DB\ISchemaWrapper;
use OCP\Migration\SimpleMigrationStep;
use OCP\Migration\IOutput;

/**
 * Add gnulinux_log table
 */
class Version001000Date20180819150256 extends SimpleMigrationStep {
	/**
	 * @param IOutput $output
	 * @param \Closure $schemaClosure The `\Closure` returns a `ISchemaWrapper`
	 * @param array $options
	 * @return null|ISchemaWrapper
	 * @since 13.0.0
	 */
	public function changeSchema(IOutput $output, \Closure $schemaClosure, array $options) {
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();

		if (!$schema->hasTable('gnulinux_log')) {
			$table = $schema->createTable('gnulinux_log');
			$table->addColumn('log_id', 'bigint', [
				'autoincrement' => true,
				'notnull' => true
			]);
			$table->addColumn('file_id', 'bigint', [
				'notnull' => true
			]);
			$table->addColumn('user_id', 'string', [
				'notnull' => true,
				'length' => 64,
			]);
			$table->addColumn('resolved', 'boolean', [
				'default' => false
			]);
			$table->setPrimaryKey(['log_id']);
			$table->addIndex(['user_id'], 'gnu_log_user');
			$table->addIndex(['file_id'], 'gnu_log_file');
			$table->addUniqueIndex(['user_id', 'file_id'], 'gnu_log_user_file');
		}

		return $schema;
	}
}
