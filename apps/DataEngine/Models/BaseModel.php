<?php

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