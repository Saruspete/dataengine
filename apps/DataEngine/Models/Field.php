<?php

/**
 *  This file is part of AMPortal, released under GNU/GPLv3
 *  See LICENSE or go to <http://www.gnu.org/licenses/> for details.
 *  Copyright (C) 2016  Adrien Mahieux
 */

namespace AMPortal\DataEngine\Models;

use Phalcon\Validation\Validator;


/*
Attributes:
  - Name : internal name of the field
  - 
*/

class Field extends BaseModel {
	
	private static $_placeholders = array();

	private $id;
	private $idPlaceholder;
	private $dateCreation;
	private $dateEdit;


	public $name;
	public $path;
	public $charset = "utf8";
	public $source;
	public $format;
	public $transformation;

	// TODO :
	public $formatSql;
	public $attributes;
	public $validators;

	/**
	 * Magic method for Phalcon
	 */
	public function initialize() {
		$this->belongsTo('placeholderId', 'AMPortal\DataEngine\Models\Placeholder', 'id', 
		array(
			'alias'	=> 'placeholder',
		));
		//$this->belongsTo('id', 'AMPortal\DataEngine\Models\CollectionFields', 'idField');
	}



	public function getId() {
		return $this->id;
	}

	public function setPlaceholder(Placeholder $ph) {
		$this->idPlaceholder = $ph->getId();
		return $this;
	}

	public function getPlaceholder() {

		// Check cache & popuplate if needed
		if (!isset(self::$_placeholders[$this->idPlaceholder])) {
			if (!$this->idPlaceholder)
				throw new \Exception("Cannot request placeholder of non saved Placeholder");
			
			// Fetch record
			self::$_placeholders[$this->idPlaceholder] = Placeholder::findFirst($this->idPlaceholder);
			
		}
		return self::$_placeholders[$this->idPlaceholder];
	}

	public function getIdPlaceholder() {
		return $this->idPlaceholder;
	}

	public function addValidation(Validator $v) {
		$this->validators[] = $v;
		return $this;
	}

}
