<?php

/**
 *  This file is part of AMPortal, released under GNU/GPLv3
 *  See LICENSE or go to <http://www.gnu.org/licenses/> for details.
 *  Copyright (C) 2016  Adrien Mahieux
 */

namespace AMPortal\TechInventory\Controllers;

use AMPortal\TechInventory\Services\ParserModule\DevicePci;


class DeviceController extends BaseController {
	
	function IndexAction() {
		
	}

	function PciDbUpdateAction() {
		$pci = new DevicePci();
		
		echo "<pre>";
		//print_r($pci->parseLspci());
		print_r($pci->parseHwids());
		echo "</pre>";
	}
}