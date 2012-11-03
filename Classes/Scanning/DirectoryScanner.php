<?php
namespace TYPO3\Zubrovka\Scanning;

/*                                                                        *
 * This script belongs to the FLOW3 framework.                            *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\FLOW3\Annotations as FLOW3;

/**
 * @FLOW3\Scope("singleton")
 */
class DirectoryScanner extends AbstractScanner {

	/**
	 * @param array $directories
	 * @return array
	 */
	public function scan(array $directories) {
		$results = array();
		foreach ($directories as $directory) {
			/** @var $iterator \RecursiveDirectoryIterator */
			$iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory));;
			while ($iterator->valid()) {
				if (!$iterator->isDot()) {
					$filename = $iterator->key();
					if ('inc' == strtolower(substr($filename, -3))) {
						$filenameResults = array();
						try {
							$this->traverser->traverse($this->parse($filename));
						} catch (\PHPParser_Error $e) {
							$results[$filename][get_class($e)] = $e->getMessage();
							$iterator->next();
							continue;
						}
						foreach ($this->traverser->getVisitors() as $analyzer) {
							$filenameResults[get_class($analyzer)] = $analyzer->getResults();
						}
						$results[$filename] = $filenameResults;
					}
				}
				$iterator->next();
			}
		}
		return $results;
	}
}