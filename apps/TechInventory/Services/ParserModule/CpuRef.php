<?php

/**
 *  This file is part of AMPortal, released under GNU/GPLv3
 *  See LICENSE or go to <http://www.gnu.org/licenses/> for details.
 *  Copyright (C) 2016  Adrien Mahieux
 */

namespace AMPortal\TechInventory\Services\ParserModule;

class CpuRef extends ParserModule {


// http://ark.intel.com/fr/compare/93791,93795,93790,93792,93801,93804,93793,93806,93811,93814,93794,84680?e=t

	public function parseIntelArkIndex($html) {

		# Create a DOM parser object
		$dom = new DOMDocument();

		# Parse the HTML from Google.
		# The @ before the method call suppresses any warnings that
		# loadHTML might throw because of invalid HTML in the page.
		@$dom->loadHTML($html);

		# Iterate over all the <a> tags
		foreach($dom->getElementsByTagName('a') as $link) {
			# Show the <a href>
			echo $link->getAttribute('href');
			echo "<br />";
		}
	}


}