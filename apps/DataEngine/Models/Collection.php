<?php

/**
 *  This file is part of AMPortal, released under GNU/GPLv3
 *  See LICENSE or go to <http://www.gnu.org/licenses/> for details.
 *  Copyright (C) 2016  Adrien Mahieux
 */

namespace AMPortal\DataEngine\Models;

/*
  A collection is a representation of one or more placeholders + associated fields
  it can be added filters. For example:
  - 
*/


class Collection extends BaseModel {

	private $id;
	private $idPlaceholderPrimary;
	
	public $name;
	public $fields;
	

	public function initialize() {
		//$this->hasMany('id', 'AMPortal\DataEngine\Models\CollectionFields', 'idCollection');
		
		// Create the relationship to link Fields through CollectionFields
		$this->hasManyToMany('id', 'AMPortal\DataEngine\Models\CollectionFields', 'idCollection',
			'idField', 'AMPortal\DataEngine\Models\Field', 'id',
			array(
				'alias'	=> 'Fields',
			)
		);

		$this->hasOne('idPlaceholderPrimary', 'AMPortal\DataEngine\Models\Placeholder', 'id', array(
			'alias'	=> 'PlaceholderPrimary'
		));
		
	}


	public function getId() {
		return $this->id;
	}

	public function getFields($parameters = null) {
		return $this->getRelated('Fields', $parameters);
	}

	/**
	 *
	 *
	 */
	public function addField(Field $field, $replace = false) {

		if (isset($this->fields[$field->name]) ) {
			if (!$replace)
				throw new \Exception('Field name "'.$field->name.'" is already reserved !');
		}

		// Add / Replace the field name
		$this->fields[$field->name] = $field;

		return $this;
	}

	/**
	 * 
	 * 
	 */
	public function addFields($fields, $replace = false) {
		foreach ($fields as $o_field) {
			$this->addField($o_field, $replace);
		}

		return $this;
	}


	public function setPlaceholderPrimary(Placeholder $ph) {
		$this->idPlaceholderPrimary = $ph->getId();
		return $this;
	}

	public function getPlaceholderPrimaryId() {
		return $this->idPlaceholderPrimary;
	}

	public function getPlaceholderPrimary() {
		return $this->getRelated('PlaceholderPrimary');
	}

	/**
	 * 
	 * @return Array Array of Placeholders
	 */
	public function getPlaceholders() {
		$a_placeholders = array();
		foreach ($this->fields as $o_field) {
			$o_ph = $o_field->getPlaceholder();
			$a_placeholders[$o_ph->getPath()] = $o_ph;
		}

		return $a_placeholders;
	}
}
