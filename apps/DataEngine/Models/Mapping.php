<?php

/**
 *  This file is part of AMPortal, released under GNU/GPLv3
 *  See LICENSE or go to <http://www.gnu.org/licenses/> for details.
 *  Copyright (C) 2016  Adrien Mahieux
 */

namespace AMPortal\DataEngine\Models;

class Mapping extends BaseModel {

	public $idCollectionSrc;
	public $idCollectionDst;
	public $idFieldSrc;
	public $idFieldDst;
	public $transformation;

}
