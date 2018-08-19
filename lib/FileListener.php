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

use OCP\Files\IRootFolder;
use OCP\Files\File;
use OCP\Files\Node;
use OCP\IUserSession;

class FileListener {
	/** @var IRootFolder */
	private $folder;

	/** @var Log */
	private $log;

	/** @var IUserSession */
	private $userSession;

	public function __construct(IRootFolder $folder, Log $log, IUserSession $userSession) {
		$this->folder = $folder;
		$this->log = $log;
		$this->userSession = $userSession;
	}

	public function listen() {
		$this->folder->listen('\OC\Files', 'postWrite', [$this, 'onWrite']);
	}

	public function onWrite(Node $node) {
		// operate on all files with a text/* mimetype
		if ($node instanceof File && $node->getMimePart() === 'text') {
			$content = $node->getContent();
			if (preg_match('/(?<!GNU\/)Linux/i', $content)) {
				$this->log->logWrite($node, $this->userSession->getUser());
			}
		}
	}
}
