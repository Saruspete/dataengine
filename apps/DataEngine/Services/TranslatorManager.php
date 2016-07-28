<?php

namespace AMPortal\DataEngine\Services;

use AMPortal\DataEngine\Models\Collection;
use AMPortal\DataEngine\Models\Connection;

class TranslatorManager extends BaseManager {

	/**
	 * Create the models and do the translation
	 *
	 */
	public function getTranslator(Connection $conn) {

		return $this->_createTranslation($conn->type);

	}
	

	public function translate(Connection $connSrc, Connection $connDst, Collection $collSrc, Collection $collDst) {

		// Get the 2 objects for the translation
		$o_trSrc = $this->getTranslator($connSrc);
		$o_trDst = $this->getTranslator($connDst);

		// Test the connections

		// Check the collection columns validity

		// Prepare the queries
		$o_trSrc->prepareExport($collSrc);
		$o_trDst->prepareImport($collDst);

		// And let's go
		while ($a_data = $o_trSrc->export()) {
			$o_trDst->import($a_data);
		}

		return true;
	}


	/**
	 *
	 *
	 */
	public function prepare(Connection $cn, Collection $cl) {
		
	}

	//public function createCollection

}