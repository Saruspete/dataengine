<?php

namespace AMPortal\DataEngine\Services;

use AMPortal\DataEngine\Models\Collection;
use AMPortal\DataEngine\Models\Connection;

class TranslatorManager extends BaseManager {

	/**
	 * Create the models and do the translation
	 *
	 */
	public function translate(Connection $connSrc, Connection $connDst, Collection $collSrc, Collection $collDst) {

		// Get the 2 objects for the translation
		$o_trSrc = $this->_createTranslation($connSrc);
		$o_trDst = $this->_createTranslation($connDst);

		// Test the connections
		if (!$o_trSrc->testConnection($connSrc))
			throw new \Phalcon\Exception("Unable to connect to Src");
		if (!$o_trSrc->testConnection($connDst))
			throw new \Phalcon\Exception("Unable to connect to Dst");


		// Check the collection columns validity
		$a_fldDst = array();
		foreach ($collDst->getFields() as $o_fldDst) {
			$a_fldDst[$o_fldDst->path] = $o_fldDst;
		}
		foreach ($collSrc->getFields() as $o_fldSrc) {
			if (empty($a_fldDst[$o_fldSrc->name]))
				throw new \Exception('Cannot find destination field for source "'.$o_fldSrc->name.'" ('.$o_fldSrc->getId().')');
		}

		// Some strategy checks
		if ($connSrc->getUid() == $connDst->getUid())
			$o_trDst->setImportStrategy("view");

		// Prepare the queries
		$o_trSrc->prepareExport($collSrc);
		$o_trDst->prepareImport($collDst);


		// Export rows
		$i_cnt = 0;
		while ($a_data = $o_trSrc->export()) {

			// Do the remapping if needed
			//$this->_applyMap(&$a_data);

			// And import the data
			$o_trDst->import($a_data);
			$i_cnt++;
		}

		return $i_cnt;
	}


	/**
	 *
	 *
	 */
	public function prepare(Connection $cn, Collection $cl) {

	}

	//public function createCollection

}