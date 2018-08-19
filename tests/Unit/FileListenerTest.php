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

use OCP\Files\File;
use OCA\GnuLinux\FileListener;
use OCA\GnuLinux\Log;
use OCP\Files\IRootFolder;
use OCP\IUser;
use OCP\IUserSession;
use Test\TestCase;

class FileListenerTest extends TestCase {
	/** @var \PHPUnit_Framework_MockObject_MockObject|Log */
	private $log;
	/** @var \PHPUnit_Framework_MockObject_MockObject|IUserSession */
	private $userSession;
	/** @var \PHPUnit_Framework_MockObject_MockObject|IRootFolder */
	private $rootFolder;
	/** @var FileListener */
	private $listener;

	public function setUp() {
		parent::setUp();

		$this->log = $this->createMock(Log::class);
		$this->userSession = $this->createMock(IUserSession::class);
		$user = $this->createMock(IUser::class);
		$this->userSession->expects($this->any())
			->method('getUser')
			->willReturn($user);
		$this->rootFolder = $this->createMock(IRootFolder::class);
		$this->listener = new FileListener($this->rootFolder, $this->log, $this->userSession);
	}

	private function getFile($mimepart, $content) {
		$file = $this->createMock(File::class);
		$file->expects($this->any())
			->method('getMimePart')
			->willReturn($mimepart);
		$file->expects($this->any())
			->method('getContent')
			->willReturn($content);

		return $file;
	}

	public function testWriteDataProvider() {
		return [
			['text', 'Linux is my favorite OS', true],
			['text', 'I use linux every day', true],
			['text', 'GNU/Linux is my favorite OS', false],
			['x-test', 'Linux is my favorite OS', false],
		];
	}

	/**
	 * @dataProvider testWriteDataProvider
	 * @param string $mimePart
	 * @param string $content
	 * @param bool $shouldLog
	 */
	public function testWriteData($mimePart, $content, $shouldLog) {
		$file = $this->getFile($mimePart, $content);
		$called = false;
		$this->log->expects($this->any())
			->method('logWrite')
			->willReturnCallback(function() use (&$called) {
				$called = true;
			});

		$this->listener->onWrite($file);

		$this->assertEquals($shouldLog, $called);
	}
}
