<?php

/**
 *  This file is part of AMPortal, released under GNU/GPLv3
 *  See LICENSE or go to <http://www.gnu.org/licenses/> for details.
 *  Copyright (C) 2016  Adrien Mahieux
 */

namespace AMPortal\TechInventory\Tasks;

use AMPortal\TechInventory\Models\Cpu;

use Phalcon\Cli\Task;

class ReferentialcpuTask extends Task {

	public function mainAction() {
		echo "This module updates the table techinv_cpus with : ". PHP_EOL;
		echo "- Intel CPUs from ARK (internet)" . PHP_EOL;
	}

	public function UpdateIntelAction() {

		$sHtmlIndex = $this->_getHttp('http://ark.intel.com');

		//
		// Extract the CPU families from Index
		//
		$docIndex = new \DOMDocument();
		@$docIndex->loadHTML($sHtmlIndex);
		$xpathIndex = new \DOMXPath($docIndex);
		$nlist = $xpathIndex->query("//div[@id='Processors-pane']//a");

		$aFamilies = array();
		$aCpuIds = array();

		foreach ($nlist as $link) {
			$sLink = $link->getAttribute('href');
			if (preg_match('#/products/family/([0-9]+)/(.+)#', $sLink, $aRes)) {
				$iFamilyId = $aRes[1];
				$sFamilyName = $aRes[2];
				$aFamilies[$iFamilyId] = $sFamilyName;
			}
		}
		
		//
		// Extract CPU IDs from family details
		//
		foreach ($aFamilies as $iFamilyId=>$sFamilyName) {
			$sHtmlFamily = $this->_getHttp('http://ark.intel.com/products/family/'.$iFamilyId.'/');

			// Extract the CPU families from Index
			$docFamily = new \DOMDocument();
			@$docFamily->loadHTML($sHtmlFamily);
			$xpathFamily = new \DOMXPath($docFamily);
			// Or /fr/products/84678/Intel-Xeon-Processor-E7-4830-v3-30M-Cache-2_10-GHz

			$aFamilyProds = $xpathFamily->query("//tr//a[@data-product-id]");
			foreach ($aFamilyProds as $oFamilyProd) {
				$iProd = $oFamilyProd->getAttribute('data-product-id');
				if ($iProd)
					$aCpuIds[] = $iProd; 
			}
		}

		//
		// Request export by pack of 25 (intel website limitation of 30)
		//
		$iCount = count($aCpuIds);
		$iStep = 25;
		$aCpus = array();
		for($i=0; $i<$iCount; $i+= $iStep) {
			//http://ark.intel.com/fr/compare/93791,93795,...,93794,84680?e=t
			$sUrl = 'http://ark.intel.com/compare/';
			$sUrl .= implode(',', array_slice($aCpuIds, $i, $iStep));
			$sUrl .= '?e=t';

			// Fetch from ARK
			$sXml = $this->_getHttp($sUrl);
			$aCpusPage = array();

			// Extract the content from (fucking) XML
			$oXml = new \SimpleXMLElement(trim($sXml));
			$aNs = $oXml->getNamespaces();
			
			// Foreach Row
			$iRow = 0;
			foreach ($oXml->xpath('//ss:Row[position() >= 3]') as $oRow) {

				// Foreach Cell
				$iCell = 0;
				$sCellKey = '';
				foreach ($oRow->xpath('ss:Cell') as $oCell) {

					$aCpu = &$aCpusPage[$iCell];

					// Get the data
					$sData = '';
					foreach ($oCell->xpath('ss:Data') as $oData) {
						 $sData .= trim((string)$oData);
					}

					// Remove special chars
					// TODO: Maybe use mb_convert_encoding();
					//$sData = trim(str_replace('‡', '', $sData));
					//$sData = trim(str_replace("\u2021", '', $sData));
					$sData = str_replace('#', 'nb', $sData);
					$sData = preg_replace('#</?sub>#', '', $sData);
					$sData = trim(preg_replace('#[^a-zA-Z0-9_\- ()/]#', '', $sData));

					// First row is the title
					if ($iRow == 0) {

						// Fetch the CPU Id from the link as :
						// http://ark.intel.com/fr/products/77779/Intel-Core-i7-4960X-Processor-Extreme-Edition-15M-Cache-up-to-4_00-GHz
						$sLink = $oCell->attributes($aNs['ss'])['HRef'];
						if ($sLink && preg_match('#/products/([0-9]+)/#', $sLink, $aLinkParts)) {
							$aCpu['id'] = $aLinkParts[1];
							$aCpu['Manufacturer'] = "Intel";
						}

						if (!empty($sData)) {
							$aCpu['Name'] = $sData;
						}
					}
					// Other rows are detail
					else {

						// First cell is the row name
						if ($iCell == 0) {
							if (!empty($sData)) 
								$sCellKey = $sData;
						}
						// Else, it's a data column
						else {
							
							// Process some special cases
							switch ($sCellKey) {
								// Split price
								case 'Recommended Customer Price':
									$aData = explode('-', $sData);
									$aCpu['priceMin'] = $aData[0];
									$aCpu['priceMax'] = (!empty($aData[1])) ? $aData[1] : $aData[0];
									
									break;

								// TODO: Transform MB/GB into MB
								case 'Max Memory Size (dependent on memory type)':
									$aData = explode(' ', $sData);
									$fSize = $aData[0];
									$sUnit = (!empty($aData[1])) ? $aData[1] : ''; 
									switch($sUnit) {
										// There's no break, and it's by intent.
										case 'TB':	$fSize *= 1024;
										case 'GB':	$fSize *= 1024;
										default:	break;
									}
									$aCpu['Max Memory Size'] = $fSize;

								// Just the key
								default: 
									$aCpu[$sCellKey] = $sData;
									break;
							}
						}
					}

					$iCell++;
				}

				$iRow++;
			}


			// Process the requested CPUs
			$aModelMapping = array(
				'id'						=> 'manufacturerId',
				'Manufacturer'				=> 'manufacturer',
				'Name'						=> 'name',
				'Code Name'					=> 'model',
				'Processor Number'			=> 'code',
				'Status'					=> 'supportStatus',
				'Expected Discontinuance'	=> 'supportDateEol',
				'Launch Date'				=> 'dateLaunch',
				'Lithography'				=> 'lithography',
//				'# of Cores'				=> 'nbCores',
//				'# of Threads'				=> 'nbThreads',
				'nb of Cores'				=> 'nbCores',
				'nb of Threads'				=> 'nbThreads',
				'Processor Base Frequency'	=> 'freqBase',
				'Max Turbo Frequency'		=> 'freqMax',
				'Bus Speed'					=> 'freqBus',
				'Cache'						=> 'cache3Size',
				'TDP'						=> 'tdp',
				'Memory Types'				=> 'memTypes',
//				'Max # of Memory Channels'	=> 'memMaxChans',
				'Max nb of Memory Channels'	=> 'memMaxChans',
				'Max Memory Bandwidth'		=> 'memMaxBw',
				'PCI Express Revision'		=> 'pcieVersion',
//				'Max # of PCI Express Lanes'=> 'pcieLanes',
				'Max nb of PCI Express Lanes'=> 'pcieLanes',
				'PCI Express Configurations'=> 'pcieConf',
				'PCI Support'				=> 'pciVersion',
				'Sockets Supported'			=> 'sockets',
//				'T<sub>CASE</sub>'			=> '',
//				'T<sub>JUNCTION</sub>'		=> '',
				// Recomputed elements
				//'Max Memory Size (dependent on memory type)'	=> 'memMaxSize',
				'Max Memory Size'			=> 'memMaxSize',
				'Recommended Customer Price'=> 'priceMin',

			);

			foreach ($aCpusPage as $aCpu) {

				// Skip empty data (first columns)
				if (empty($aCpu['id'])) {
					continue;
				}



				// Create object
				$oCpu = Cpu::findFirst("manufacturer='".$aCpu['Manufacturer']."' AND manufacturerId = '".$aCpu['id']."'");
				if (!$oCpu)
					$oCpu = new Cpu();

				$bChanged = false;
				foreach ($aModelMapping as $sKeyText=>$sKeyModel) {
					// Skip non-existant keys
					if (!isset($aCpu[$sKeyText]))
						continue; 

					// Add key only if needed
					if ($oCpu->{$sKeyModel} != $aCpu[$sKeyText]) {
						$oCpu->{$sKeyModel}  = $aCpu[$sKeyText];
						$bChanged = true;
					}
				}
				// Something changed, save it
				if ($bChanged) {
					if (!$oCpu->save()) {
						echo "Error during save of CPU :", print_r($aCpu, true), PHP_EOL;
						print_r($oCpu->getMessages());
					}
				}

			}
		}

/*
		$aCpusFlags = array();
		echo "== ", count($aCpus), PHP_EOL;
		foreach ($aCpus as $aCpu) {
			if (is_array($aCpu)) {
				$aCpusFlags = array_merge($aCpusFlags, $aCpu);
			}
		}

		print_r($aCpusFlags);
		echo "Count: ", count($aCpus);
		
*/
	}






	protected function _getHttp($url) {

		$sCacheDir = '/var/tmp/techinv/cache/';
		if (!is_dir($sCacheDir))
			mkdir($sCacheDir, 0700, true);

		$sCacheFile = $sCacheDir.md5($url);

		if (is_file($sCacheFile)) {
			$sData = file_get_contents($sCacheFile);
		}
		else {
			$sData = file_get_contents($url);
			file_put_contents($sCacheFile, $sData);
		}

		return $sData;


		$ch = curl_init();
		$timeout = 5;

		// TODO: Add proxy management
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		$html = curl_exec($ch);
		curl_close($ch);

		return $html;
	}

}