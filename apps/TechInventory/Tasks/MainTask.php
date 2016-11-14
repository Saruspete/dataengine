<?php

/**
 *  This file is part of AMPortal, released under GNU/GPLv3
 *  See LICENSE or go to <http://www.gnu.org/licenses/> for details.
 *  Copyright (C) 2016  Adrien Mahieux
 */

use Phalcon\Cli\Task;

class MainTask extends Task
{
	public function mainAction() {
		echo "This is the default task and the default action" . PHP_EOL;
	}

	/**
	 * @param array $params
	 */
	public function testAction(array $params)
	{

		$dispatcher = $this->getDI()->getShared('dispatcher');
		$task       = $dispatcher->getTaskName();
		$action     = $dispatcher->getActionName();

		echo PHP_EOL;
	}
}