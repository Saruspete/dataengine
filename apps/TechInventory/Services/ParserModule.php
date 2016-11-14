<?php

/**
 *  This file is part of AMPortal, released under GNU/GPLv3
 *  See LICENSE or go to <http://www.gnu.org/licenses/> for details.
 *  Copyright (C) 2016  Adrien Mahieux
 */

namespace AMPortal\TechInventory\Services;

abstract class ParserModule extends BaseService {

	abstract public function getCapabilities();


	/* 
	Source path. Can be one of:
	file://

	*/
	protected $source;
	protected $content;

	public function getContent() {

	}

	public function getContentFromSource() {

	}

	public function setContent($content) {
		$this->content = $content;
	}


	// ====================================================
	//
	// Text processing helpers
	//
	// ====================================================
	
	protected function getParagraphs($text) {
		
		$return = array();
		preg_match_all('#(.+?)(?:\n\n|$)#s', $text, $return);
		return $return[1];
	}

	protected function getGroupsByIndentation($text) {
		$lines = array();
		$return = array();
		preg_match_all('#(\s*)(.*)#', $text, $lines);

		foreach ($lines as $lnum=>$line) {

		}

	}

	protected function getGroupsBySeparator($text, $separator = ':') {

		// Parse each line
		preg_match_all('#^(.+?)'.$separator.'(.+)$#', $text, $aGroups);
		foreach ($aGroups as $aGroup) {
			$sKey = trim($aGroup[1]);
			$sVal = trim($aGroup[2]);

			$aResults[$sKey] = $sVal;
		}

		return $aResults;
	}

	/**
	 *
	 *
	 */
	protected function escapeDblQuotes($text) {
		return str_replace('"', '\"', $text);
	}

}