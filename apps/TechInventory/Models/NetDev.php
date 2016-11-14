<?php

/**
 *  This file is part of AMPortal, released under GNU/GPLv3
 *  See LICENSE or go to <http://www.gnu.org/licenses/> for details.
 *  Copyright (C) 2016  Adrien Mahieux
 */

namespace AMPortal\TechInventory\Models;

class NetDev extends BaseModel {
	private $id;

	public $idParent;
	public $idAsset;
	public $idDevice;

	public $name;
	public $type;
	public $subtype;
	public $serial;
	public $port;
	public $connectedTo;
	public $cdp;
}