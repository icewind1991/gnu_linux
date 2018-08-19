<?php
/**
 * @copyright Copyright (c) 2018 Robin Appelman <robin@icewind.nl>
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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace OCA\GnuLinux;

use OCP\Files\File;
use OCP\IDBConnection;
use OCP\IUser;

class Log {
	/** @var IDBConnection */
	private $connection;

	public function __construct(IDBConnection $connection) {
		$this->connection = $connection;
	}

	public function logWrite(File $file, IUser $user) {
		if (!$this->entryExists($file, $user)) {
			$query = $this->connection->getQueryBuilder();
			$query->insert('gnulinux_log')
				->values([
					'file_id' => $query->createNamedParameter($file->getId()),
					'user_id' => $query->createNamedParameter($user->getUID())
				]);
			$query->execute();
		}
	}

	private function entryExists(File $file, IUser $user) {
		$query = $this->connection->getQueryBuilder();
		$query->select('log_id')
			->from('gnulinux_log')
			->where($query->expr()->eq('file_id', $query->createNamedParameter($file->getId())))
			->andWhere($query->expr()->eq('user_id', $query->createNamedParameter($user->getUID())));

		$result = $query->execute();
		return $result->fetch();
	}

	public function getLog() {
		$query = $this->connection->getQueryBuilder();
		$query->select('log_id', 'user_id', 'file_id')
			->from('gnulinux_log');

		$result = $query->execute();
		return $result->fetchAll();
	}

	public function clear() {
		$query = $this->connection->getQueryBuilder();
		$query->delete('gnulinux_log');
		$query->execute();
	}
}
