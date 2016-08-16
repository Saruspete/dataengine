<?php

/**
 *  This file is part of AMPortal, released under GNU/GPLv3
 *  See LICENSE or go to <http://www.gnu.org/licenses/> for details.
 *  Copyright (C) 2016  Adrien Mahieux
 */

namespace AMPortal\DataEngine\Controllers;

class SyncController extends ControllerBase {

	public function indexAction() {
	


	}


	public function newsyncAction() {
		
	}

	public function syncAction($cnSrc, $cnDst, $clSrc, $clDst) {

		$connSrc = AMPortal\DataEngine\Models\Connection::findFirst($cnSrc);
		$connDst = AMPortal\DataEngine\Models\Connection::findFirst($cnDst);

		$collSrc = AMPortal\DataEngine\Models\Collection::findFirst($clSrc);
		$collDst = AMPortal\DataEngine\Models\Collection::findFirst($clDst);

		// Translate from source
		echo "<h1>Translation</h1>";
		$app['de_trstmgr']->translate($connSrc, $connDst, $collSrc, $collDst);

/*
		// Get the generated profiles from the profiler
		$profiles = $di->get('dbProfiler')->getProfiles();

		foreach ($profiles as $profile) {
			echo "SQL Statement: ", $profile->getSQLStatement(), "\n";
			echo "Start Time: ", $profile->getInitialTime(), "\n";
			echo "Final Time: ", $profile->getFinalTime(), "\n";
			echo "Total Elapsed Time: ", $profile->getTotalElapsedSeconds(), "\n";
		}
*/

	
	}

}