<?php

namespace AMPortal\DataEngine\Services;

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
		//echo "Roflmao from ", get_class($this),"\n<br />";
		return $this;
	}



}