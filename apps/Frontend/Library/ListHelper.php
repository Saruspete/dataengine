<?php

namespace AMPortal\Frontend\Library;

use Phalcon\Tag;

use AMPortal\Frontend\Models\ListTable;
use AMPortal\Frontend\Models\ListField;


class ListHelper extends Tag {

	/**
	 * Sort the fields according to their position
	 */
	protected function _sortFields(&$aFields) {
		usort($aFields, function($a,$b) {
			return $a->position - $b->position;
		});
	}


	protected function _getValue(&$aData, $sField, $mDefault = NULL) {
		if (isset($aData[$sField]))
			return $aData[$sField];
		return $mDefault;
	}

	/**
	 * Check if the $sField field exists in $aData and if it has a "true" value
	 * If not defined, returns $bDefault 
	 *
	 * @return boolean
	 */
	protected function _isEnabled(&$aData, $sField, $bDefault = true) {
			if (!isset($aData[$sField]))
				return $bDefault;

			return ($aData[$sField] != false);
	}


	protected function _isSelected(&$aData, $sKey, $sValue, $bDefault = true) {
		// If not defined, assume default
		if (!isset($aData[$sKey]))
			return $bDefault;

		return (!is_array($aData[$sKey]) || in_array($oField, $aData[$sKey]));
	}



	/**
	 * Get user-selected filter
	 *
	 */



	/**
	 * Generate the HTML Header of the table
	 * 
	 * @return string HTML
	 */
	protected function _genHtmlHead(ListTable &$oTable, array &$aData, &$params) {

		$sHtml = '<thead><tr>';

		// Process every field
		$iFieldNum = 0;

		$aFields = $oTable->getFields();


		//$bFilterShow	= $this->_isEnabled($params, 'filter');
		$sTName 		= $oTable->name;
		$sTNameDisp 	= ($oTable->nameDisplay || ucfirst($sTName));


		foreach ($aFields as $oField) {
			
			$sFName = $oField->name;
			$sFNameDisp = (!empty($oField->nameDisplay)) 
				? $oField->nameDisplay
				: ucfirst($sFName); 

			// Add the Field name
			$sHtml .= '<th>'.$sFNameDisp;

			// Add the field filter
			//if ($bFilterShow) {
				// Only add filter if listed or non filtered
				if ($this->_isSelected($params, 'filter', $sFName)) {
					$sHtml .= '<input type="text" name="filter|'.$sTName.'|'.$sFName.'" value="" />';
				}
			//}

			$sHtml .= '</th>';
			$iFieldNum++;
		}

		$sHtml .= '</tr></thead>';

		return $sHtml;
	}

	/**
	 * Generate the table body.
	 */
	protected function _genHtmlBody(ListTable &$oTable, array &$aData, &$params) {
		

		$sHtml = '<tbody>';

		// Empty element
		$sDataEmpty = "";

		$bRowAlternate = $this->_isEnabled($params, 'rowAlternate');
		$bColAlternate = $this->_isEnabled($params, 'colAlternate', false);

		$aFields = $oTable->getFields();
		
		// Foreach row
		foreach ($aData as $sKey=>$aRow) {

			$sHtml .= '<tr>';

			//if ($this->)

			// Foreach column
			foreach ($aFields as $oField) {

				$sData = (isset($aRow[$oField->name])) ? $aRow[$oField->name] : $sEmpty;
				$sHtml .= '<td>'. $sData.'</td>';
			}

			$sHtml .= '</tr>';
		}

		return $sHtml;
	}


	/**
	 * Generate the html header with fields and columns
	 */
	public function genHtml(ListTable $oTable, array &$aData, $params = false) {
		
		$bTableShow	= $this->_isEnabled($params, 'table');
		$bHeadShow	= $this->_isEnabled($params, 'head');

		$sHtml = '';
		//return var_dump($oTable) . var_dump($oTable->getFields());

		// Show head before table
		if ($bHeadShow)
			$sHtml .= '<h4>'.$oTable->name.'</h4>';
		
		// Show table
		if ($bTableShow) {
			$sHtml .= '<table '. $this->_getValue($params, 'tableAttr') .'>';
		}

		// Generate table head
		$sHtml .= $this->_genHtmlHead($oTable, $aData, $params);
		// Generate table body
		$sHtml .= $this->_genHtmlBody($oTable, $aData, $params);
		
		if ($bTableShow)
			$sHtml .= '</table>';

		return $sHtml;
	}

}