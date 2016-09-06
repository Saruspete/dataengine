<?php

/**
 *  This file is part of AMPortal, released under GNU/GPLv3
 *  See LICENSE or go to <http://www.gnu.org/licenses/> for details.
 *  Copyright (C) 2016  Adrien Mahieux
 */

namespace AMPortal\DataEngine\Controllers;

use Phalcon\Mvc\Controller;
use Phalcon\Mvc\View;

class ControllerBase extends Controller {

	/**
	 *
	 */
	protected function _ajaxDisplayList($a_list, $a_params = false) {

		$this->view->setRenderLevel(View::LEVEL_NO_RENDER);

		if (!$a_params)
			$a_params = array('name');

		$a_results = array();

		foreach ($a_list as $o_obj) {
			$a_data = array(
				'id'	=> $o_obj->getId(),
			);
			foreach ($a_params as $s_param) {
				$a_data[$s_param] = $o_obj->{$s_param};
			}

			$a_results[] = $a_data;
		}

		return json_encode($a_results);
	}

}
