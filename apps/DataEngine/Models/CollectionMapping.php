<?php

/**
 *  This file is part of AMPortal, released under GNU/GPLv3
 *  See LICENSE or go to <http://www.gnu.org/licenses/> for details.
 *  Copyright (C) 2016  Adrien Mahieux
 */

namespace AMPortal\DataEngine\Models;

/*
  A mapping is a link or transformation of 2 collections' fields. Ex:
  - C1.F1 => C2.F4
  - C1.PlainText == sha512() ==> C2.HashedPass

*/


class CollectionMapping extends BaseModel {

	private $id;
	public $idCollectionSrc;
	public $idCOllectionDst;


	public function initialize() {
		$this->belongsTo('idCollectionSrc', 'AMPortal\DataEngine\Models\Collection', 'id', 
			array(
				'alias'	=> 'CollectionSrc',
			)
		);
		$this->belongsTo('idCollectionDst', 'AMPortal\DataEngine\Models\Collection', 'id',
			array(
				'alias'	=> 'CollectionDst',
			)
		);

	}

}