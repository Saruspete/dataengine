<?php

/**
 *  This file is part of AMPortal, released under GNU/GPLv3
 *  See LICENSE or go to <http://www.gnu.org/licenses/> for details.
 *  Copyright (C) 2016  Adrien Mahieux
 */

namespace AMPortal\DataEngine\Controllers;

use Phalcon\Mvc\View;
use AMPortal\DataEngine\Models\Collection;
use AMPortal\DataEngine\Models\CollectionFields;
use AMPortal\DataEngine\Models\Connection;
use AMPortal\DataEngine\Models\Placeholder;
use AMPortal\DataEngine\Models\Field;
use AMPortal\DataEngine\Services\DiscoveryManager;

use Phalcon\Forms\Form;
use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\Select;

class CollectionController extends ControllerBase {

	public function indexAction() {}


	// ====================================================
	// 
	//  Collection Editor
	// 
	// ====================================================

	public function editorAction() {

		$o_form = new Form();


		// Check if we have some post data to save
		if ($this->request->isPost()) {

			$s_op     = $this->request->getPost('op');
			$m_collId = $this->request->getPost('collection');
			$i_collId = 0;
			$a_fields = $this->request->getPost('fields');
			$i_phPrim = $this->request->getPost('phPrim');

			// Is an INT, check for its ID
			// Is a STRING, create a new one
			if (!empty($m_collId)) {
				if (is_numeric($m_collId)) {
					$o_cl = Collection::findFirst((int)$m_collId);
					$i_collId = (int)$m_collId;
				}
				
				// Delete existing collection
				if ($s_op == 'Delete') {
					
				}

				// Save changes
				elseif ($s_op == 'Save') {

					// Save a new collection
					if (!$i_collId && is_string($m_collId)) {
						$o_cl = new Collection();
						$o_cl->name = $m_collId;
						$o_cl->charset = "utf8";
						$o_cl->setPlaceholderPrimaryId($i_phPrim);

						if (!$o_cl->save()) {
							$s_flashMsg = 'Error during save of new collection "'.$m_collId.'" :';
							foreach ($o_cl->getMessages() as $s_msg) {
								$s_flashMsg .= " : ".$s_msg;
							}
							$this->flash->error($s_flashMsg);
						}
						else {
							$i_collId = $o_cl->getId();
							$this->flash->success('Successfully saved new collection "'.$m_collId.'" (id: '.$i_collId.')');
						}
					}

					if (!empty($i_collId)) {
						// Search all collectionFields
						$a_collFields = array(); 
						foreach (CollectionFields::find('idCollection = "'.$i_collId.'"') as $o_cf) {
							$a_collFields[$i_collId][$o_cf->idField] = $o_cf;
						}

						// Add / Check existing fields
						foreach ($a_fields as $i_fid) {
							$a_added = array('success' => array(), 'error' => array());

							// Found existing CollectionField. 
							if (!empty($a_collFields[$i_collId][$i_fid])) {
								// Remove if from the list
								unset($a_collFields[$i_collId][$i_fid]);
							}
							else {
								$o_cf = new CollectionFields();
								$o_cf->idCollection = $i_collId;
								$o_cf->idField = $i_fid;
								if (!$o_cf->save()) {
									$this->flash->error('Error during save of CollectionField "'.$i_collId.'/'.$i_fid.'")');
								}
								else {
									$a_added['success'][] = $o_cf->idField;
								}
							}
						}

						// Now remove existing but not sent fields
						$a_deleted = array('success' => array(), 'error' => array());
						foreach ($a_collFields[$i_collId] as $i_fid=>$o_fld) {
							if ($o_fld->delete()) {
								$a_deleted['success'][] = $o_fld->idField;
							}
							else {
								$a_deleted['error'][] = $o_fld->idField;
							}
						}

						if (count($a_added['success']))
							$this->flash->success('Successfully added fields: '.implode(',', $a_added['success']));

						if (count($a_deleted['success']))
							$this->flash->success('Successfully removed fields: '.implode(',', $a_deleted['success']));
						if (count($a_deleted['error']))
							$this->flash->error('Error during removal of fields: '.implode(',', $a_deleted['error']));

					}
				}

			}
		}

		// Create the form
		$o_form->add(new Select('phPrim', array() ) );


		// Set variables
		$this->view->setVar('form', $o_form);

		// Add header CSS
		$this->assets
			->addCss('css/multiselect.css')
			->addCss('css/select2.min.css')
			->addCss('css/dataengine.css');

		// Add footer JS
		$this->assets
			->addJs('js/multiselect.js')
			->addJs('js/select2.min.js')
			->addJs('js/DataEngine-Collection-editor.js');

	}


	/**
	 * 
	 */
	public function editorAjaxGetPlaceholdersAction() {
		$this->view->setRenderLevel(View::LEVEL_NO_RENDER);

		$a_results = array();

		// Fetch every placeholder and its fields
		$a_placeholders = Placeholder::find();
		$i = 0;
		foreach ($a_placeholders as $o_ph) {

			$i_phid = $o_ph->getId();

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

	/**
	 *
	 */
	public function editorAjaxGetCollectionsAction() {

		echo $this->_ajaxDisplayList(Collection::find());
	
	}

	/**
	 *
	 */
	public function editorAjaxGetCollectionDetailsAction($i_id) {
		$this->view->setRenderLevel(View::LEVEL_NO_RENDER);

		$c = Collection::findFirst($i_id);
		$a_placeholders = array();
		$a_fields = array();

		foreach ($c->getFields() as $o_field) {

			$i_phid = $o_field->idPlaceholder;

			// New placeholder
			if ( empty($a_placeholders[$i_phid]) ) {
				$o_ph = $o_field->getPlaceholder();
				$a_placeholders[$i_phid] = array(
					'id'	=> $o_ph->getId(),
					'name'	=> $o_ph->name,
					'path'	=> $o_ph->path,
					'fields'=> array(),
				);
			}

			$a_placeholders[$i_phid]['fields'][] = array(
				'id'			=> $o_field->getId(),
				'idPlaceholder'	=> $o_field->idPlaceholder,
				'name'			=> $o_field->name,
				'path'			=> $o_field->path,
				'source'		=> $o_field->source,
				'format'		=> $o_field->format,
			);
		}

		$a_results = array(
			'name'		=> $c->name,
			'idPhPrim'	=> $c->getPlaceholderPrimaryId(),
			'placeholders' => array(),
		);

		foreach ($a_placeholders as $a_ph) {
			$a_results['placeholders'][] = $a_ph;
		}

		echo json_encode($a_results);

	}


	// ====================================================
	// 
	//  Placeholder discovery
	// 
	// ====================================================

}

