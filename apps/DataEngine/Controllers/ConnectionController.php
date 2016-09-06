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

use Phalcon\Forms\Form;
use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\Select;

class ConnectionController extends ControllerBase {

	public function indexAction() {}

	/**
	 *
	 *
	 */
	public function editorAction() {

		$s_results = '';
		$a_fields = array('type', 'name', 'username', 'password', 'host', 'schema', 'extra');
		$a_types = array(
			'MySQL' => 'MySQL',
			'Oracle' => 'Oracle',
			'MSSQL' => 'MSSQL'
		);
		$a_fldVals = array();


		foreach ($a_fields as $s_field) {
			$v = $this->request->getPost($s_field);
			$a_fldVals[$s_field] = (!empty($v)) ? $v : '';
		}

		


		//
		// Check for operation from POST input
		// 
		if ($this->request->isPost()) {

			$s_op = $this->request->getPost('op');
			$i_cnId = $this->request->getPost('id');

			$o_cn = $this->_GetConnectionFromPost($i_cnId);

			// Test a connection without saving it
			if ($s_op == 'Test') {


				$a_cres = $this->_TestConnection($o_cn);

				if ($a_cres['return'] == true) {
					$this->flash->success("Connection successfull");
					$s_results .= "Connection successful !";
					if (!$o_cn->getId())
						$s_results .= '(don\'t fortget to save it !)';
				}
				else {
					$this->flash->error('Error during connection test');
					$s_results .= "Connection error: ".$a_cres['error'];
				}
				
			}
			// Save the connection credentials
			else if ($s_op == 'Save') {

				if (!$o_cn->save()) {
					$this->flash->error('Unable to save connection "'.$o_cn->name.'" ');
					
					foreach ($o_cn->getMessages() as $s_msg) {
						$s_results .= "\n".$s_msg;
					}
				}
				else {
					$this->flash->success('Connection successfully saved. ID:'.$o_cn->getId());
				}
			}
			else if ($s_op == 'Delete') {
				if (!$o_cn->delete()) {
					$s_flashmsg = 'Unable to delete connection "'.$o_cn->name.'" ';
					foreach ($o_cn->getMessages() as $s_msg) {
						$s_flashmsg .= " : ".$s_msg;
					}
					$this->flash->error($s_flashmsg);
				}
				else {
					$this->flash->success('Connection "'.$o_cn->name.'" deleted');
				}
			}
			// (re)discover 
			else if ($s_op == 'Discover') {
				// Check if the connection is valid

				$b_error = false;
				
				$o_mgr = new DiscoveryManager($this->di);

				if (!$o_cn->getId() && !$o_cn->save()) {

					$this->flash->error('Unable to save connection "'.$o_cn->name.'" ');
					foreach ($o_cn->getMessages() as $s_msg) {
						$s_results .= "\n".$s_msg;
					}
					$b_error = true;
				}

				// TODO : Do more display processing
				if ($o_mgr->discover($o_cn)) {
					$this->flash->success('Discovery successful');
				}	
			}

			// WTF
			else {
				$this->flash->error("Unknown action '".$s_op."'");
			}

		}

		// Create the selection content
		$a_conns = array(0 => '(New Connection)');

		foreach (Connection::find() as $o_conn) {
			$s_cname = $o_conn->name.' ('. $o_conn->type.'://'.$o_conn->username.'@'.$o_conn->hostname;
			if ($o_conn->hostport)
				$s_cname .= ':'.$o_conn->hostport;
			$s_cname .= ')';
			$a_conns[$o_conn->getId()] = $s_cname;
		}


		// Create the form and fields
		$o_form = new Form();
		//$o_form->bind($_POST);
		$o_form->add(new Select('id', $a_conns, ['value' => $a_fldVals['id'] ]));
		$o_form->add(new Select('type', $a_types, ['value' => $a_fldVals['type'] ]));
		
		$o_form->add(new Text('name'));
		$o_form->add(new Text('username'));
		$o_form->add(new Text('password'));
		$o_form->add(new Text('hostname'));
		$o_form->add(new Text('hostport'));
		$o_form->add(new Text('resource'));
		$o_form->add(new Text('extra'));

		// Display editor
		$this->view->setVar('form', $o_form);
		$this->view->setVar('results', $s_results);
		$this->view->setVar('values', $a_fldVals);


		// Add header CSS
		$this->assets
			->addCss('css/select2.min.css')
			->addCss('css/dataengine.css');

		// Add footer JS
		$this->assets
			->addJs('js/select2.min.js')
			->addJs('js/DataEngine-Connection-editor.js');


	}


	/**
	 * Ajax helper to get the connections list
	 */
	public function editorAjaxListConnectionsAction() {

		echo $this->_ajaxDisplayList(Connection::find());

	}

	/**
	 * Ajax helper to get the connection details
	 */
	public function editorAjaxGetConnectionInfoAction($i_cid) {

		echo $this->_ajaxDisplayList(
			Connection::find($i_cid),
			array('name','type','username','hostname','hostport','resource','extra')
		);
	}

	/**
	 * 
	 * 
	 */
	public function editorAjaxTestConnection() {
		$this->view->setRenderLevel(View::LEVEL_NO_RENDER);

		echo json_encode($this->_TestConnection());
	}


	/**
	 *
	 *
	 */
	private function _TestConnection(Connection $cn = null) {
		$o_discover = new \AMPortal\DataEngine\Services\DiscoveryManager($this->di);


		$a_result = array('error' => '', 'return' => true);
		try {

			// If no cn, get it from the post args
			if (!$cn)
				$cn = $this->_GetConnectionFromPost();

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
	private function _GetConnectionFromPost($i_cnId = NULL) {

		// Get the POST elements
		$a_opts = array('name', 'type', 'username', 'password', 'host', 'schema', 'extra');
		$a_vars = array();
		foreach ($a_opts as $s_opt) {
			//if (!$this->request->getPost($s_opt)))
			$a_vars[$s_opt] = $this->request->getPost($s_opt);
		}

		$a_hostdata = explode(':', $a_vars['host']);
		$host = $a_hostdata[0];
		$port = (!empty($a_hostdata[1])) ? $a_hostdata[1] : 0; 

		// Create or load the requested connection
		if ($i_cnId)
			$cn = Connection::findFirst($i_cnId);
		else
			$cn = new Connection();

		// fill / update the values
		$cn->name = $a_vars['name'];
		$cn->type = $a_vars['type'];
		$cn->hostname = $host;
		$cn->hostport = $port;

		$cn->username = $a_vars['username'];
		if (isset($a_vars['pass']) && $a_vars['password'] != '(unchanged)')
			$cn->password = $a_vars['pass'];
		
		$cn->resource = $a_vars['schema'];
		$cn->extra = $a_vars['extra'];

		return $cn;
	}



}

