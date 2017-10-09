<?php

/**
 *  This file is part of AMPortal, released under GNU/GPLv3
 *  See LICENSE or go to <http://www.gnu.org/licenses/> for details.
 *  Copyright (C) 2016  Adrien Mahieux
 */

namespace AMPortal\Frontend\Library;

use Phalcon\Mvc\User\Component;


/**
 * Elements
 *
 * Helps to build UI elements for the application
 */
class Navigation extends Component {

	// Tree data containing the structure
	private $_data;

	// Index for active route
	private $_index;


	protected function _dataGet($sMenuName) {

		// Init data for first time
		if(!$this->_data[$sMenuName])
			$this->_dataInit($sMenuName);

		return $this->_data[$sMenuName];
	}


	protected function _dataInit($sMenuName) {

		/*
			navigationName => array(
				// Mandatory Parameters
				'caption'	=> string.

				'routeName'		=> 
				'routeParams'	=> array

				// Excluvise with routeName
				'routePath'		=> string

				// Optionnal Parameters 
				'position'	=> int
				'children'	=> array
				'cssClasses'=> array				 
			)
		*/

		// Check for cache
		if (false) {

		}
		else {

			$auth = $this->session->get('auth');

			// Call default values
			//$this->_data = array();
			//$this->_data = array_merge_recursive($this->_data, $this->_getModulesDef());
			$this->_data = $this->_getModulesDef();

			// Process data
			$this->_dataParse($this->_data);

			// Index menu for quick search
			$this->_index = $this->_dataIndex($this->_data);
		}
	}

	/**
	 * Parse and process supplied data array
	 *
	 */
	protected function _dataParse(&$aData, $sPrefix='') {

		// Init instances to build routes
		$oDi = $this->getDi();
		$oRoute = $oDi->get('router');
		$oUrl = $oDi->get('url');
		$i = 0;

		foreach($aData as $sName=>&$mData) {
			$i++;

			// Do following processing only level > 1
			if (isset($aData['__root'])) {
				$this->_dataParse($mData);
			}
			else {
				// Pointer to previous level
				if(empty($mData['parent']))
					$mData['parent'] = &$aData;

				// Add caption by the key name
				if(empty($mData['caption']))
					$mData['caption'] = ucfirst($sName);

				if (!isset($mData['position']))
					$mData['position'] = $i;

				// non-set specific route && named route: Generate corresponding link
				if (empty($mData['routePath']) && !empty($mData['routeName'])) {

					// Default to empty params
					if (empty($mData['routeParams']))
						$mData['routeParams'] = array();

					//$oRoute->getRouteByName($mData['routeName']);
					
					// Generate the route-path from 
					$mData['routePath'] = $oUrl->get(
						array_merge(
							$mData['routeParams'], 
							array('for' => $mData['routeName'])
						)
					);

				}
				// If no link, create at least an empty anchor for display
				if (empty($mData['routePath']))
					$mData['routePath'] = '#';

				// Recurse on children
				if (!empty($mData['children']))
					$this->_dataParse($mData['children']);

			}
		}
	}
	

	/**
	 *
	 */
	protected function _dataIndex(&$aTree) {
		$aIndex = array();

		foreach ($aTree as $sKey=>&$aData) {
			// Do following processing only level > 1
			if (isset($aTree['__root'])) {
				$aIndex[$sKey] = $this->_dataIndex($aData);
			}
			// 
			else {
				if (!empty($aData['routePath'])) {
					//$aIndex[$sPrefix.$sKey] = &$aData;
					$aIndex[$aData['routePath']] = &$aData;
				}

				if (isset($aData['children']))
					$aIndex = array_merge($aIndex, $this->_dataIndex($aData));
			}
		}

		return $aIndex;
	}


	/**
	 * Load definition from other modules
	 *
	 */
	protected function _getModulesDef() {
		$oDi = $this->getDi();
		$aModElements = array('__root' => array());

		foreach ($oDi->getShared('application')->getModules() as $aModName=>$aMod) {
			// mod has 2 keys: className & path
			$sDefFile = dirname($aMod['path']).'/config/navigation.php';
			if (is_file($sDefFile)) {
				$aModElements = array_merge_recursive($aModElements, include($sDefFile));
			}
		}
		return $aModElements;
	}






	/**
	 * Recursive function to generate the menu html
	 * 
	 */
	protected function _menuGenHtml($data) {

		if (empty($data))
			return "";
		
		// Sort current level by position
		usort($data, function($a,$b) {
			return $a['position'] - $b['position'];
		});

		$ret = '';

		// Iterate on the menu structure
		foreach ($data as $sName=>$aMenu) {
			$bChild = (!empty($aMenu['children']));

			// Base list element
			if (!empty($aMenu['cssClasses'])) {
				$ret .= '<li class="'.implode(' ', $aMenu['cssClasses']).'">';
			}
			else {
				$ret .= '<li>';
			}

			// Construct local element piece by piece
			$sElem = '';
			$sAExt = '';

			if (!empty($aMenu['caption']))
				$sElem .= $aMenu['caption'];

			if ($bChild) {
				$sElem .= ' <b class="caret"></b>';
				$sAExt = 'class="dropdown-toggle" data-toggle="dropdown"';
			}

			// If we have a routePath (url) available
			if (!empty($aMenu['routePath']))
				$sElem = '<a href="'.$aMenu['routePath'].'" '.$sAExt.'>'.$sElem.'</a>';

			$ret .= $sElem;

			// Recursion on children
			if (!empty($aMenu['children'])) {
				$ret .= '<ul class="dropdown-menu">';
				$ret .= $this->_menuGenHtml($aMenu['children']);
				$ret .= '</ul>';
			}
		
			$ret .= '</li>';
		}

		return $ret;
	}




	/**
	 * Builds header menu with left and right items
	 *
	 * @return string
	 */
	public function getMenu($sMenuName) {

		$oDi = $this->getDi();

		// Init data
		$aData = $this->_dataGet($sMenuName);

		$aUrls = array_keys($this->_index[$sMenuName]);
		// Generate the active path on the menu
		/*
		$oActiveRoute = $oDi->get('router')->getMatchedRoute();
		echo "==== Active route: ", print_r($oActiveRoute, true);
		$sRoutePattern = $oActiveRoute->getPattern();

		*/
		/*
    [_pattern:protected] => /TechInventory/:params
    [_compiledPattern:protected] => #^/TechInventory(/.*)*$#u
    [_paths:protected] => Array
        (
            [namespace] => AMPortal\TechInventory\Controllers
            [module] => TechInventory
            [controller] => index
            [action] => index
            [params] => 1
        )

    [_methods:protected] => 
    [_hostname:protected] => 
    [_converters:protected] => 
    [_id:protected] => 8
    [_name:protected] => TechInventory
    [_beforeMatch:protected] => 
    [_match:protected] => 
    [_group:protected] => 
		*/
		
		//$controllerName = $this->view->getControllerName();

		return $this->_menuGenHtml($aData);
	}

	/**
	 * Returns menu tabs
	 */
	public function getTabs()
	{
		$controllerName = $this->view->getControllerName();
		$actionName = $this->view->getActionName();
		echo '<ul class="nav nav-tabs">';
		echo '</ul>';
	}
}

/**
<a id="dLabel" role="button" data-toggle="dropdown" class="btn btn-primary" data-target="#" href="/page.html">
        Dropdown <span class="caret"></span>
    </a>
	<ul class="dropdown-menu multi-level" role="menu" aria-labelledby="dropdownMenu">
      <li><a href="#">Some action</a></li>
      <li><a href="#">Some other action</a></li>
      <li class="divider"></li>
      <li class="dropdown-submenu">
        <a tabindex="-1" href="#">Hover me for more options</a>
        <ul class="dropdown-menu">
          <li><a tabindex="-1" href="#">Second level</a></li>
          <li class="dropdown-submenu">
            <a href="#">Even More..</a>
            <ul class="dropdown-menu">
                <li><a href="#">3rd level</a></li>
            	<li><a href="#">3rd level</a></li>
            </ul>
          </li>
          <li><a href="#">Second level</a></li>
          <li><a href="#">Second level</a></li>
        </ul>
      </li>
    </ul>
*/