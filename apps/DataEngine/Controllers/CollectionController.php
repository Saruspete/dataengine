<?php

/**
 *  This file is part of AMPortal, released under GNU/GPLv3
 *  See LICENSE or go to <http://www.gnu.org/licenses/> for details.
 *  Copyright (C) 2016  Adrien Mahieux
 */

namespace AMPortal\DataEngine\Controllers;

use Phalcon\Mvc\View;
use AMPortal\DataEngine\Models\Collection;
use AMPortal\DataEngine\Models\Connection;
use AMPortal\DataEngine\Models\Placeholder;
use AMPortal\DataEngine\Models\Field;
use AMPortal\DataEngine\Services\DiscoveryManager;

class CollectionController extends ControllerBase {

	public function indexAction() {}


	// ====================================================
	// 
	//  Collection Editor
	// 
	// ====================================================

	/**
	 *
	 *
	 */
	public function editorAction() {

		// Check if we have some post data to save
		if ($this->request->isPost()) {

			$m_collId = $this->request->getPost('collection');
			$a_fields = $this->request->getPost('fields');


			// Is an INT, check for its ID
			// Is a STRING, create a new one
			if (!empty($m_collId)) {
				if (is_int($m_collId)) {
					$o_cl = Placeholder::find((int)$m_collId);

				}
				elseif (is_string($m_collId)) {
					$o_cl = new Placeholder();
					$o_cl->name = $m_collId;
				}


				foreach ($a_fields as $i_fid=>$s_fname) {

				}
			}
			
		}



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

	/**
	 *
	 */
	public function discoverAction() {

		$s_results = '';
		$a_fields = array('Name', 'User', 'Pass', 'Host', 'Schema', 'Extra');
		$a_fldVals = array();

		foreach ($a_fields as $s_field) {
			$v = $this->request->getPost($s_field);
			$a_fldVals[$s_field] = (!empty($v)) ? $v : '';
		}


		// Check for POST input
		if ($this->request->isPost()) {

			$s_op = $this->request->getPost('op');
			$i_cnId = $this->request->getPost('connectionId');
			$o_cn = $this->_discoverGetConnectionFromPost($i_cnId);

			// Test a connection without saving it
			if ($s_op == 'Test') {

				$a_cres = $this->_discoverTestConnection($o_cn);

				if ($a_cres['return'] == true) {
					$this->flash->success("Connection successfull");
					$s_results .= "Connection successful !";
				}
				else {
					$this->flash->error($a_cres['error']);
					$s_results .= "Connection error: ".$a_cres['error'];
				}
				
			}
			// Save the connection credentials
			else if ($s_op == 'Save') {

				if (!$o_cn->save()) {
					$s_flashmsg = 'Unable to save connection "'.$o_cn->name.'" ';
					foreach ($o_cn->getMessages() as $s_msg) {
						$s_flashmsg .= " : ".$s_msg;
					}
					$this->flash->error($s_flashmsg);
				}
			}
			// (re)discover 
			else if ($s_op == 'Discover') {
				// Check if the connection is valid

				
				$o_mgr = new DiscoveryManager($this->di);

				$o_mgr->discover($o_cn);
	
			}

			// WTF
			else {
				$this->flash->error("Unknown action '".$s_op."'");
			}

		}

		// Display editor
		$this->view->setVar('connections', Connection::find());
		$this->view->setVar('conntypes', array('MySQL', 'Oracle', 'MSSQL'));
		$this->view->setVar('results', $s_results);
		$this->view->setVar('values', $a_fldVals);


		// Add header CSS
		$this->assets
			->addCss('css/select2.min.css')
			->addCss('css/dataengine.css');

		// Add footer JS
		$this->assets
			->addJs('js/select2.min.js')
			->addJs('js/DataEngine-Collection-discover.js');


	}


	/**
	 * Ajax helper to get the connections list
	 */
	public function discoverAjaxListConnectionsAction() {

		echo $this->_ajaxDisplayList(Connection::find());

	}

	/**
	 * Ajax helper to get the connection details
	 */
	public function discoverAjaxGetConnectionInfoAction($i_cid) {

		echo $this->_ajaxDisplayList(
			Connection::find($i_cid),
			array('name','type','username','hostname','hostport','resource','extra')
		);
	}

	/**
	 * 
	 * 
	 */
	public function discoverAjaxTestConnection() {
		$this->view->setRenderLevel(View::LEVEL_NO_RENDER);

		echo json_encode($this->_discoverTestConnection());
	}


	/**
	 *
	 *
	 */
	private function _discoverTestConnection(Connection $cn = null) {
		$o_discover = new \AMPortal\DataEngine\Services\DiscoveryManager($this->di);


		$a_result = array('error' => '', 'return' => true);
		try {

			// If no cn, get it from the post args
			if (!$cn)
				$cn = $this->_discoverGetConnectionFromPost();

			$o_discover->testConnection($cn);

		}
		catch (\Exception $e) {
			$a_result['return'] = false;
			$a_result['error'] = $e->getMessage().' ('.$e->getCode().')<br /><pre>'.$e->getTraceAsString().'</pre>';
		}

		return $a_result;
	}

	/**
	 * Get a connection object from user POST'ed fields
	 * @return Connection
	 */
	private function _discoverGetConnectionFromPost($i_cnId = NULL) {

		// Get the POST elements
		$a_opts = array('Name', 'Type', 'User', 'Pass', 'Host', 'Schema', 'Extra');
		$a_vars = array();
		foreach ($a_opts as $s_opt) {
			//if (!$this->request->getPost($s_opt)))
			$a_vars[$s_opt] = $this->request->getPost($s_opt);
		}

		$a_hostdata = explode(':', $a_vars['Host']);
		$host = $a_hostdata[0];
		$port = (!empty($a_hostdata[1])) ? $a_hostdata[1] : 0; 

		$cn = new Connection();
		$cn->name = $a_vars['Name'];
		$cn->type = $a_vars['Type'];
		$cn->hostname = $host;
		$cn->hostport = $port;
		$cn->username = $a_vars['User'];

		$cn->password = ($a_vars['Pass'] == "(unchanged)") ? NULL : $a_vars['Pass'];
		$cn->resource = $a_vars['Schema'];
		$cn->extra = $a_vars['Extra'];

		return $cn;
	}


	// ====================================================
	// 
	//  Private stubs
	// 
	// ====================================================

	/**
	 *
	 */
	private function _ajaxDisplayList($a_list, $a_params = false) {

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

