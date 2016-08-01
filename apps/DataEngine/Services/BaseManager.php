<?php

namespace AMPortal\DataEngine\Services;

use AMPortal\DataEngine\Models\Connection;

class BaseManager extends BaseService {


	protected function _createTranslation(Connection $c) {

		$s_connectorName = __NAMESPACE__."\Translators\\".$c->type."\Translation";

		return new $s_connectorName($this->_di, $c);
	}

	protected function _createDiscovery(Connection $c) {

		$s_connectorName = __NAMESPACE__."\Translators\\".$c->type."\Discovery";

		return new $s_connectorName($this->_di, $c);
	}
}