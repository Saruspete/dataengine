<?php

/**
 *  This file is part of AMPortal, released under GNU/GPLv3
 *  See LICENSE or go to <http://www.gnu.org/licenses/> for details.
 *  Copyright (C) 2016  Adrien Mahieux
 */

namespace AMPortal\DataEngine\Models;

class CollectionFields extends BaseModel {

	//public $idCollection;
	//public $idField;

	public function initialize() {
		$this->belongsTo('idCollection', 'AMPortal\DataEngine\Models\Collection', 'id',
			array(
				'alias'	=> 'Collection'
			)
		);
		$this->belongsTo('idField', 'AMPortal\DataEngine\Models\Field', 'id',
			array(
				'alias'	=> 'Field'
			)
		);
	}

}