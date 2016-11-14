<?php

/**
 *  This file is part of AMPortal, released under GNU/GPLv3
 *  See LICENSE or go to <http://www.gnu.org/licenses/> for details.
 *  Copyright (C) 2016  Adrien Mahieux
 */

namespace AMPortal\TechInventory\Models;

class Device extends BaseModel {
	
	private $id;

	public $name;
	public $model;
	public $modelCode;
	public $manufacturer;
	public $manufacturerCode;
	public $vendor;
	public $vendorCode;

	/*
	public $powerConsumption;

	*/
}