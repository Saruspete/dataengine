<?php

/**
 *  This file is part of AMPortal, released under GNU/GPLv3
 *  See LICENSE or go to <http://www.gnu.org/licenses/> for details.
 *  Copyright (C) 2016  Adrien Mahieux
 */

namespace AMPortal\DataEngine\Models;

use Phalcon\Mvc\Model;

class BaseModel extends Model {

	/**
	 * Override the default table name and add prefix "dataengine_"
	 */
	public function getSource() {
		// Beware ! This will only work within a namespace. 
		return 'dataengine_'.strtolower( substr( strrchr(get_class($this), '\\'), 1) );
	}

}