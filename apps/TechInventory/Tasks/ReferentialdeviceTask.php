<?php

/**
 *  This file is part of AMPortal, released under GNU/GPLv3
 *  See LICENSE or go to <http://www.gnu.org/licenses/> for details.
 *  Copyright (C) 2016  Adrien Mahieux
 */

namespace AMPortal\TechInventory\Tasks;

use AMPortal\TechInventory\Models\Device;

use Phalcon\Cli\Task;

class ReferentialdeviceTask extends Task {

	public function mainAction() {
		echo "This module updates the table techinv_cpus with : ". PHP_EOL;
		echo "- Intel CPUs from ARK (internet)" . PHP_EOL;
	}

	public function UpdateDeviceAction() {
		$sDb = '/usr/share/hwdata/pci.ids';
		if (!is_file($sDb))
			$sDb = 'pci.ids';

		// Parsing elements
		$sHex = '[0-9a-f]{2,4}';
		$sDelimClass = '# List of known device classes, subclasses and programming interfaces';

		// Arrays of data we are working on
		$aLines = file($sDb);
		$aManufacturers = array();
		$aDevicesDb = Device::find();
		$aDevicesHw = array();
		
		// Temporary values from parsing
		$sManufacturer = NULL;
		$sManufacturerCode = NULL;
		$sVendor = NULL;
		$sVendorCode = NULL;
		$sModel = NULL;
		$sModelCode = NULL;


		// First pass : get only manufacturers (for subvendors list)
		foreach ($aLines as $sLine) {
			$cStart = $sLine[0];
			// skip non manufacturer lines with simple tests
			if ($cStart == "#" || $cStart == "\t" || empty(trim($sLine)))
				continue;

			if (preg_match('#^('.$sHex.') (.+)$#', $sLine, $aData)) {
				$sManufacturerCode = $aData[1];
				$sManufacturer     = $aData[2];
				$aManufacturers[$sManufacturerCode] = $sManufacturer;
			}
		}


		// Second pass : Loop for all devices
		$bListDevices = true;
		foreach ($aLines as $sLine) {

			// Skip comments
			if ($sLine[0] == "#") {
				if (trim($sLine) == $sDelimClass)
					$bListDevices = false;
				continue;
			}

			preg_match('#^(?:C )?(\t*)('.$sHex.')( '.$sHex.')?(.+?)$#', $sLine, $aData);

			// Skip non matching elements
			if (empty($aData[2]))
				continue;

			$sLevel   = $aData[1];
			$sCodePri = trim($aData[2]);
			$sCodeSec = trim($aData[3]);
			$sDesc    = trim($aData[4]);


			$iLvl = (!empty($sLevel)) ? substr_count($sLevel, "\t") : 0;

			// Devices list
			if ($bListDevices) {

				if ($iLvl == 0) {
						$sManufacturerCode = $sCodePri;
						$sManufacturer = $sDesc;
				}
				elseif ($iLvl == 1 || $iLvl == 2) {

					// Model
					if ($iLvl == 1) {
						$sModelCode = $sCodePri;
						$sModel = $sDesc;

						$sVendor = NULL;
						$sVendorCode = NULL;

					}

					// Submodel (vendor)
					else {
//						print_r($aData);
						$sModelCode = $sCodeSec;
						$sVendorCode = $sCodePri;
						$sModel = $sDesc;
						if (isset($aManufacturers[$sVendorCode])) {
							$sVendor = $aManufacturers[$sVendorCode];
						}
						else {
							echo "Unknown manufacturer $sVendorCode from subsystem $sModelCode (", trim($sLine), ")\n";
						}
					}

					// Add device to the list
					$sDevId = $sManufacturerCode.':'.$sModelCode.':'.$sVendorCode;
					$aDevicesHw[$sDevId] = array(
						'manufacturer'		=> $sManufacturer,
						'manufacturerCode'	=> $sManufacturerCode,
						'model'				=> $sModel,
						'modelCode'			=> $sModelCode,
						'vendor'			=> $sVendor,
						'vendorCode'		=> $sVendorCode,
					);
					
				}
				// Warning: WTF is that ? 
				else {

				}

				
			}
			// Classes list
			else {

			}
		}


		echo "Got ", count($aDevicesDb), " in DB\n";

		// Now, compare to what we have in database
		foreach ($aDevicesDb as $oDevDb) {
			$sDevId = $oDevDb->manufacturerCode.':'
					. $oDevDb->modelCode.':'
					. $oDevDb->vendorCode;

			// Known device, check for update
			if (isset($aDevicesHw[$sDevId])) {
				$aDevHw = $aDevicesHw[$sDevId];
				$bDevChg = false;

				// Check / Update values
				if ($oDevDb->manufacturer != $aDevHw['manufacturer']) {
					$oDevDb->manufacturer =  $aDevHw['manufacturer'];
					$bDevChg = true;
				}
				if ($oDevDb->model != $aDevHw['model']) {
					$oDevDb->model  = $aDevHw['model'];
					$bDevChg = true;
				}
				if ($oDevDb->vendor != $aDevHw['vendor']) {
					$oDevDb->vendor  = $aDevHw['vendor'];
					$bDevChg = true;
				}

				// Save if 
				if ($bDevChg)
					if (!$oDevDb->save())
						echo "Error during update of device '$sDevId'\n";


				// Remove the saved device from input list
				unset($aDevicesHw[$sDevId]);
			}
		}

		echo "Must insert ", count($aDevicesHw), " new\n";

		// Finally, create missing devices
		foreach ($aDevicesHw as $aDevHw) {
			$oDevice = new Device();
			$oDevice->manufacturer 		= $aDevHw['manufacturer'];
			$oDevice->manufacturerCode	= $aDevHw['manufacturerCode'];
			$oDevice->model				= $aDevHw['model'];
			$oDevice->modelCode 		= $aDevHw['modelCode'];
			$oDevice->vendor			= $aDevHw['vendor'];
			$oDevice->vendorCode 		= $aDevHw['vendorCode'];
			
			if (!$oDevice->save())
				echo "Error during save of new device ".print_r($oDevice, true)."\n"; 
		}				
	}
}