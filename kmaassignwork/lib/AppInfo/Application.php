<?php
declare(strict_types=1);
// SPDX-FileCopyrightText: Lucy Mills <ct040407@actvn.edu.vn>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\KmaAssignWork\AppInfo;

use OCP\AppFramework\App;

class Application extends App {
	public const APP_ID = 'kmaassignwork';

	public function __construct() {
		parent::__construct(self::APP_ID);
	}
}
