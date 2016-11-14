<?php

/**
 *  This file is part of AMPortal, released under GNU/GPLv3
 *  See LICENSE or go to <http://www.gnu.org/licenses/> for details.
 *  Copyright (C) 2016  Adrien Mahieux
 */

namespace AMPortal\TechInventory\Services\ParserModule;

use AMPortal\TechInventory\Services\ParserModule;
use AMPortal\TechInventory\Models\Device;
use AMPortal\TechInventory\Models\LinkPci;


class DevicePci extends ParserModule {

	/**
	 * Return the capabilities and associated functions
	 *
	 */
	public function getCapabilities() {
		return [
			'cmds'	=> [
				'lspci'	=> 'parseLspci',
			],
		);
	}


	/**
	 * Parses the output of lspci utility
	 *
	 */
	public function parseLspci($data) {

		$aDevices = array();
		$aParagraphs = $this->getParagraphs($data);

		// If only 1 paragraph, it's a simple output of lspci (no verbosity)
		if (count($aParagraphs) <= 2)
			$aParagraphs = explode("\n", $aParagraphs[0]);

		// Each paragraph of lspci is a device
		foreach ($aParagraphs as $sDevice) {

			// Some flags
			$bLastWasCollapsed = false;
			$iLastLvl = 0;
			$iLastId = 0;

			$aDevice = array(
				'key'	=> '',
				'val'	=> '',
				'chld'	=> array(),
			);
			$aIndex = array();

			$sDevPciBus = '';
			$sDevType = '';
			$sDevTypeCode = '';
			$sDevDetail = '';
			$sDevModel = '';
			$sDevModelCode = '';
			$sDevVendor = '';
			$sDevVendorCode = '';

			// 
			preg_match_all('#([\t]*)(.+)#', $sDevice, $aLinesParts, PREG_SET_ORDER);
			foreach ($aLinesParts as $aLineParts) {
				$iLvl = substr_count($aLineParts[1], "\t");
				$sLine = $aLineParts[2];
				$aLineRes = array();

				// Device name and PCI Addr
				if ($iLvl == 0) {
					// <PCI-Addr> <Device Type>: <Constructor> <Device Name>
					
					$sPtrnGrp = '#([0-9a-f:\.]+) (.+?): (.+)$#';
					preg_match($sPtrnGrp, $sLine, $aLineRes);

					// Fill the device base info
					$sDevPciBus = $aLineRes[1];
					$sDevType   = $aLineRes[2];
					$sDevDetail = $aLineRes[3];

					$aDevice['key'] = trim($sDevPciBus);
					$aDevice['val'] = trim($sLine);
					$aIndex[0] = &$aDevice;

					// 04:00.0 0108: 144d:a802 (rev 01) (prog-if 02 [NVM Express])
					// 04:00.0 Non-Volatile memory controller [0108]: Samsung Electronics Co Ltd NVMe SSD Controller [144d:a802] (rev 01) (prog-if 02 [NVM Express])
 
					// Extract Hex IDs from line
					$sHex     = '[0-9a-f]{4}';
					$sHex1    = '(?:'.$sHex.'|\['.$sHex.'\])';
					$sHex2    = '(?:'.$sHex.':'.$sHex.'|\['.$sHex.':'.$sHex.'\])';
					$sPtrnIds = '#^'.$sDevPciBus.' '.'(.*)('.$sHex1.'): (.*)('.$sHex2.')( .+)?$#';
					
					// Try to get the Codes for more precision
					if (preg_match($sPtrnIds, $sLine, $aDetails)) {
						print_r($aDetails);
						$sDevType		= $aDetails[1];
						$sDevTypeCode	= $aDetails[2];
						$sDevDetail		= $aDetails[3];
						$aDevDetailCode	= explode(':', $aDetails[4]);
						$sDevVendorCode	= $aDevDetailCode[0];
						$sDevModelCode	= $aDevDetailCode[1];
					}
					
				}
				// Device features enumeration
				else {

					$parent = &$aIndex[$iLvl-1];

					// Check for "Feature: state" key
					if (preg_match('#^(.+?):(.*)$#', $sLine, $aLineRes)) {

						$sKey = $aLineRes[1];
						$sVal = $aLineRes[2];

						// Add the feature definition
						$parent['chld'][] = array(
							'key'	=> $sKey,
							'val'	=> $sVal,
							'chld'	=> array(),
						);

						// Save for collapsing
						$iLastId = count($parent['chld']) -1;
						$bLastWasCollapsed = false;

						$aIndex[$iLvl] = &$parent['chld'][$iLastId];
					}
					// No ":" in the line, and good format for collapsing it with previous
					elseif ( ($iLvl-1 == $iLastLvl) || 
							 ($iLvl == $iLastLvl && $bLastWasCollapsed) ) {

						// If last line was also collapse, parent is 2 levels before
						if ($bLastWasCollapsed)
							$parent = &$aIndex[$iLvl-2];

						// Collapse
						$parent['val'] .= " ".$sLine;

						$bLastWasCollapsed = true;
					}
					// Wow, what's that ?
					else {
echo "WOW, Unhandled line $sLine\n";
					}
				}

				$iLastLvl = $iLvl;

			}

			// Create or find the base device
			$sQuery = ' model=""';
			if ($sDevVendor)
			$oDev = Device::find($sQuery);

			if (!$oDev) {
				$oDev = new Device();
				//$oDev->model = 
			}


			// Add device to final list
			$aDevices[] = $aDevice;

			unset($parent);
		}

		return $aDevices;
	}

}