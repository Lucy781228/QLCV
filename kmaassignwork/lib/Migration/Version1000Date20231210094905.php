<?php

declare(strict_types=1);

/**
 * @copyright Copyright (c) 2023 Your name <your@email.com>
 *
 * @author Your name <your@email.com>
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace OCA\KmaAssignWork\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

/**
 * Auto-generated migration step: Please modify to your needs!
 */
class Version1000Date20231210094905 extends SimpleMigrationStep {

	/**
	 * @param IOutput $output
	 * @param Closure $schemaClosure The `\Closure` returns a `ISchemaWrapper`
	 * @param array $options
     * @return null|ISchemaWrapper
	 */
	public function preSchemaChange(IOutput $output, Closure $schemaClosure, array $options): void {
	}


	public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ?ISchemaWrapper {
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();

		// comment
		if (!$schema->hasTable('kma_comment')){
			$table = $schema->createTable('kma_comment');
			$table->addColumn('comment_id', 'integer', [
				'notnull' => true,
				'length' => 64,
				'autoincrement' => true,
			]);

			$table->addColumn('task_id', 'string', [
				'notnull' => true,
				'length' => 64,
				'default' => '',
			]);

			$table->addColumn('user_create', 'string', [
				'notnull' => true,
				'length' => 64,
			]);

			$table->addColumn('message', 'text', [
				'notnull' => true,
				'default' => '',
			]);

            $table->setPrimaryKey(['comment_id']);
        }

		// connection
		if (!$schema->hasTable('kma_connection')){
			$table = $schema->createTable('kma_connection');
			$table->addColumn('connection_id', 'integer', [
				'notnull' => true,
				'length' => 64,
				'autoincrement' => true,
			]);

			$table->addColumn('task_id', 'string', [
				'notnull' => true,
				'length' => 64,
				'default' => '',
			]);

			$table->addColumn('file_id', 'integer', [
				'notnull' => true,
				'length' => 64,
			]);

            $table->setPrimaryKey(['connection_id']);
        }

		return $schema;
    }
    /**
	 * @param IOutput $output
	 * @param Closure $schemaClosure The `\Closure` returns a `ISchemaWrapper`
	 * @param array $options
	 */
	public function postSchemaChange(IOutput $output, Closure $schemaClosure, array $options): void {
	}
}
