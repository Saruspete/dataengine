<?php

/**
 *  This file is part of AMPortal, released under GNU/GPLv3
 *  See LICENSE or go to <http://www.gnu.org/licenses/> for details.
 *  Copyright (C) 2016  Adrien Mahieux
 */

namespace AMPortal\DataEngine\Models;

/*
  A placeholder is a path to a container. For example :
  - SQL : DB + Table
  - CSV : File Path
  - XLS : File path + worksheet

*/

class Placeholder extends BaseModel {

	/**
	 * ID of the placeholder
	 * 
	 * @Primary
	 * @Identity
	 * @Column(type="integer", nullable=false)
	 */
	private $id;
	public $name;

	public $path;
	public $type;
	public $rowsCount;
	public $sourceUid;

	protected $fields;

	public function getId() {
		return $this->id;
	}

	public function initialize() {

		$this->hasMany("id", 'AMPortal\DataEngine\Models\Field', 'idPlaceholder', array(
				'alias'	=> 'Fields'
		));

	}

	// Fields management
	public function addFields($fields) {
		foreach ($fields as $o_field) {
			$this->addField($o_field);
		}
		return $this;
	}

	public function addField(Field $field) {
		$this->fields[$field->name] = $field;
		return $this;
	}

	public function getField($fieldPath) {
		return $this->fields[$fieldPath];
	}

	public function getFields($parameters = null) {
		return $this->getRelated('Fields', $parameters);
	}
}
