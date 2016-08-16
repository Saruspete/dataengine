<?php

/**
 *  This file is part of AMPortal, released under GNU/GPLv3
 *  See LICENSE or go to <http://www.gnu.org/licenses/> for details.
 *  Copyright (C) 2016  Adrien Mahieux
 */

namespace AMPortal\DataEngine\Models;

class Workflow extends BaseModel {

	public $name;

	// List of elements
	protected $srcElements;
	protected $dstElements;

	/**
	 *  Execute the synchronization between the 2 collections
	 *
	 */
	public function synchronize() {




	} 

}