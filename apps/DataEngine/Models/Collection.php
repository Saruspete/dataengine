<?php

namespace AMPortal\DataEngine\Models;

/*
  A collection is a representation of one or more placeholders + associated fields
  it can be added filters. For example:
  - 
*/


class Collection extends BaseModel {

	/**
	 * @Type('integer')
	 * @Signed(false)
	 * @PrimaryKey
	 */
	private $id;

	/**
	 * @Type('varchar')
	 * @
	 *
	 */
	public $name;

	
	private $fields;
	

	public function initialize() {
		$this->hasMany('id ', 'AMPortal\DataEngine\Models\Field', 'placeholder_id');
		$this->hasMany('id', 'AMPortal\DataEngine\Models\Placeholder', 'placeholder_id');

	}


	public function getFields() {
		return $this->fields;
	}

	public function addField(Field $field, $replace = false) {

		if (isset($this->fields[$field->name]) ) {
			if (!$replace)
				throw new \Exception('Field name "'.$field->name.'" is already reserved !');
		}

		// Add / Replace the field name
		$this->fields[$field->name] = $field;

		return $this;
	}

	public function addFields($fields, $replace = false) {
		foreach ($fields as $o_field) {
			$this->addField($o_field, $replace);
		}

		return $this;
	}

	public function getPlaceholders() {
		$a_placeholders = array();
		foreach ($this->fields as $o_field) {
			$o_ph = $o_field->getPlaceholder();
		} 
	}
}
