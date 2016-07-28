<?php

namespace AMPortal\DataEngine\Models;

use Phalcon\Validation\Validator;


/*
Attributes:
  - Name : internal name of the field
  - 
*/

class Field extends BaseModel {
	
	private $id;
	private $idPlaceholder;
	private $dateCreation;
	private $dateEdit;

	private $_placeholder;

	public $name;
	public $path;
	public $charset = "utf8";
	public $source;
	public $type;
	public $attributes;
	public $transformation;
	public $validators;

	public function initialize() {
		$this->belongsTo('placeholderId', 'AMPortal\DataEngine\Models\Placeholder', 'id');
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
		if (!$this->_placeholder) {
			if ($this->idPlaceholder)
				$this->_placeholder = Placeholder::findFirst($this->idPlaceholder);
		}
		return $this->_placeholder;
	}

	public function addValidation(Validator $v) {
		$this->validators[] = $v;
		return $this;
	}

}
