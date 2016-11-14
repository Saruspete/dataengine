<?php

/**
 *  This file is part of AMPortal, released under GNU/GPLv3
 *  See LICENSE or go to <http://www.gnu.org/licenses/> for details.
 *  Copyright (C) 2016  Adrien Mahieux
 */

use Phalcon\Cli\Task;

class MainTask extends Task {
	
	public function mainAction() {
		echo "Frontend main action" . PHP_EOL;
		echo PHP_EOL;
		echo "Available modules tasks :";
		foreach (glob(__DIR__.'/../*/tasks') as $dir) {

		}
	}

}

