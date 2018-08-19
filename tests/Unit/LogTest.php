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

namespace OCA\gnulinux\tests\Unit;

use OCA\GnuLinux\Log;
use OCP\IUser;
use OCP\Files\File;
use Test\TestCase;

/**
 * @group DB
 */
class LogTest extends TestCase {
	/** @var Log */
	private $log;

	public function setUp() {
		parent::setUp();

		$this->log = new Log(\OC::$server->getDatabaseConnection());

		$this->log->clear();
	}

	public function tearDown() {
		parent::tearDown();

		$this->log->clear();
		return;
	}

	/**
	 * @param $id
	 * @return \PHPUnit_Framework_MockObject_MockObject|IUser
	 */
	private function getUser($id) {
		$user = $this->createMock(IUser::class);
		$user->expects($this->any())
			->method('getUID')
			->willReturn($id);

		return $user;
	}

	/**
	 * @param $id
	 * @return \PHPUnit_Framework_MockObject_MockObject|File
	 */
	private function getFile($id) {
		$file = $this->createMock(File::class);
		$file->expects($this->any())
			->method('getId')
			->willReturn($id);

		return $file;
	}

	public function testWriteLog() {
		$user = $this->getUser('test1');
		$file = $this->getFile(10);

		$this->log->logWrite($file, $user);

		$entries = $this->log->getLog();
		$this->assertCount(1, $entries);
		$this->assertEquals('test1', $entries[0]['user_id']);
		$this->assertEquals(10, $entries[0]['file_id']);
	}

	public function testWriteDoubleLog() {
		$user = $this->getUser('test1');
		$file = $this->getFile(10);

		$this->log->logWrite($file, $user);
		$this->log->logWrite($file, $user);

		$entries = $this->log->getLog();
		$this->assertCount(1, $entries);
	}

	public function testClear() {
		$user = $this->getUser('test1');
		$file = $this->getFile(10);

		$this->log->logWrite($file, $user);
		$this->log->clear();

		$entries = $this->log->getLog();
		$this->assertCount(0, $entries);
	}
}
