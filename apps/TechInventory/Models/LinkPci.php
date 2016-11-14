<?php

/**
 *  This file is part of AMPortal, released under GNU/GPLv3
 *  See LICENSE or go to <http://www.gnu.org/licenses/> for details.
 *  Copyright (C) 2016  Adrien Mahieux
 */

namespace AMPortal\TechInventory\Models;

class LinkPci extends BaseModel {
	
	public $idAsset;
	public $idDevice;

	public $pciId;
	public $pciLinkCap;
	public $pciLinkType
	
}