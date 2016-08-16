<?php

/**
 *  This file is part of AMPortal, released under GNU/GPLv3
 *  See LICENSE or go to <http://www.gnu.org/licenses/> for details.
 *  Copyright (C) 2016  Adrien Mahieux
 */

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