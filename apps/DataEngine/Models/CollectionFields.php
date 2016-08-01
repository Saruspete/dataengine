<?php

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