<?php

namespace AMPortal\DataEngine\Services;

class BaseManager extends BaseService {


	protected function _createTranslation($type) {

		$s_connectorName = __NAMESPACE__."\Translators\\$type\Translation";

		return new $s_connectorName($this->_di);
	}

	protected function _createDiscovery($type) {

		$s_connectorName = __NAMESPACE__."\Translators\\$type\Discovery";

		return new $s_connectorName($this->_di);
	}
}