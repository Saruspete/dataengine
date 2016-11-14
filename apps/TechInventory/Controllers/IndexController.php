<?php

/**
 *  This file is part of AMPortal, released under GNU/GPLv3
 *  See LICENSE or go to <http://www.gnu.org/licenses/> for details.
 *  Copyright (C) 2016  Adrien Mahieux
 */

namespace AMPortal\TechInventory\Controllers;

use AMPortal\TechInventory\Services\ParserModule\DevicePci;


class IndexController extends BaseController {
	
	function IndexAction() {

		$pci = new DevicePci();
		$pci->setContent(trim(file_get_contents('lspci_nn.txt')));

		echo "<pre>";
		//print_r($pci->parseLspci());
		print_r($pci->parseHwids());
		echo "</pre>";
	}
}