<?php

namespace AMPortal\DataEngine\Services;

use AMPortal\DataEngine\Models\Connection;

interface InterfaceTranslator {
	public function testConnection(Connection $c);
	public function getConnectionUid(Connection $c);
	
}