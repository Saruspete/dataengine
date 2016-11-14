<?php

/**
 *  This file is part of AMPortal, released under GNU/GPLv3
 *  See LICENSE or go to <http://www.gnu.org/licenses/> for details.
 *  Copyright (C) 2016  Adrien Mahieux
 */

namespace AMPortal\TechInventory\Services;

use Phalcon\Di;

class BaseService {

	protected $_di;

	public function setDi(Di $di) {
		$this->_di = $di;
		return $this;
	}

	public function getDi() {
		return $this->_di;
	}

	public function __construct(Di $di = null) {
		$this->_di = $di;
		
		return $this;
	}

	/**
	 * Sync to insert/update multiple models
	 */
	protected function syncModels(array $oModels, array $aModelsData) {

		// Result structure for information
		$aReturn = array(
			'added'		=> array(),
			'updated'	=> array(),
			'deleted'	=> array(),
			'errors'	=> array(),
		);

		

	}

}