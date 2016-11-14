<?php

/**
 *  This file is part of AMPortal, released under GNU/GPLv3
 *  See LICENSE or go to <http://www.gnu.org/licenses/> for details.
 *  Copyright (C) 2016  Adrien Mahieux
 */

/**
 * Register application modules
 */
$application->registerModules(array(
	'Frontend'	=> array(
		'className'	=> 'AMPortal\Frontend\Module',
		'path'		=> __DIR__ . '/../apps/Frontend/Module.php'
	),
	'DataEngine' => array(
		'className'	=> 'AMPortal\DataEngine\Module',
		'path'		=> __DIR__ . '/../apps/DataEngine/Module.php'
	),
	'TechInventory' => array(
		'className' => 'AMPortal\TechInventory\Module',
		'path'		=> __DIR__ . '/../apps/TechInventory/Module.php'	
	),
));
