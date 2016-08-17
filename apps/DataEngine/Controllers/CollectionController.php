<?php

/**
 *  This file is part of AMPortal, released under GNU/GPLv3
 *  See LICENSE or go to <http://www.gnu.org/licenses/> for details.
 *  Copyright (C) 2016  Adrien Mahieux
 */

namespace AMPortal\DataEngine\Controllers;

use Phalcon\Mvc\View;
use AMPortal\DataEngine\Models\Placeholder;
use AMPortal\DataEngine\Models\Field;

class CollectionController extends ControllerBase {

	public function indexAction() {

	}

	/**
	 *
	 *
	 */
	public function editorAction() {
		// Add header CSS
		$this->assets->collection('header')
			->addCss('css/multiselect.css');

		// Add footer JS
		$this->assets->collection('footer')
			->addJs('js/multiselect.js')
			->addJs('js/DataEngine-Collection-editor.js');

	}

	/**
	 * 
	 */
	public function editorAjaxLoadAction() {
		$this->view->setRenderLevel(View::LEVEL_NO_RENDER);

		$a_results = array();

		// Fetch every placeholder and its fields
		$a_placeholders = Placeholder::find();
		$i = 0;
		foreach ($a_placeholders as $o_ph) {

			$i_phid = (int)$o_ph->getId();

			// Need this, because json_encode requires sequential elements
			// Else, it's output as object instead of array

			//$a_results[$i_phid] = array(
			$a_results[$i] = array(
				'name'		=> $o_ph->name,
				'path'		=> $o_ph->path,
				'count'		=> $o_ph->rowsCount,
				'id'		=> $i_phid,
			);

			foreach ($o_ph->getFields() as $o_field) {
				$a_results[$i]['fields'][] = array(
					'id'		=> $o_field->getId(),
					'name'		=> $o_field->name,
					'path'		=> $o_field->path,
					'format'	=> $o_field->format,
					'source'	=> $o_field->source,
					'attributes'=> $o_field->attributes,
				);
			}

			$i++;
		}

		echo json_encode($a_results);
	}

	public function editorAddAction() {

	}

}

