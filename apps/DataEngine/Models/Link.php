<?php

/**
 *  This file is part of AMPortal, released under GNU/GPLv3
 *  See LICENSE or go to <http://www.gnu.org/licenses/> for details.
 *  Copyright (C) 2016  Adrien Mahieux
 */

namespace AMPortal\DataEngine\Models;

class Link extends BaseModel {

	public $name;

	/**
	 * @Primary
	 * @Column(type="integer", nullable=false)
	 */
	public $idPlaceholderSrc;

	/**
	 * @Primary
	 * @Column(type="integer", nullable=false)
	 */
	public $idPlaceholderDst;

	public $idFieldSrc0;
	public $idFieldSrc1;
	public $idFieldSrc2;
	public $idFieldSrc3;
	public $idFieldSrc4;
	public $idFieldSrc5;
	
	public $idFieldDst0;
	public $idFieldDst1;
	public $idFieldDst2;
	public $idFieldDst3;
	public $idFieldDst4;
	public $idFieldDst5;


	public $linkType;


	public function getFields() {

		$a_fields = array();
		
		for ($i=0; $i<6; $i++) {
			$i_src = $this->{'idFieldSrc'.$i};
			$i_dst = $this->{'idFieldDst'.$i};

			// Skip on empty fields
			if (empty($i_src) || empty($i_dst))
				continue;

			$a_fields[$i_src] = $i_dst;
		}

		return $a_fields;
	}
	

}