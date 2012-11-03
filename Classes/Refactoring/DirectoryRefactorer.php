<?php
namespace TYPO3\Zubrovka\Refactoring;

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
class DirectoryRefactorer extends CodeRefactorer {


	/**
	 * Contains prettyPrinter
	 *
	 * @var \PHPParser_PrettyPrinter_TYPO3CGL
	 */
	protected $prettyPrinter;

	/**
	 * @var array
	 */
	protected $directories;

	/**
	 * @param array $directories
	 * @return DirectoryRefactorer
	 */
	public function setDirectories(array $directories) {
		$this->directories = $directories;
		return $this;
	}

	/**
	 * @param array $directories
	 * @return array
	 */
	public function refactor() {
		gc_enable(); // Enable Garbage Collector
		var_dump(gc_enabled()); // true
		$results = array();
		foreach ($this->directories as $directory) {
			/** @var $iterator \RecursiveDirectoryIterator */
			$iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory));;
			while ($iterator->valid()) {
				if (!$iterator->isDot()) {
					$filename = $iterator->key();
					if ('php' == strtolower(substr($filename, -3))) {
						try {
							$this->load(file_get_contents($filename));
							$objectives = $this->analyze();
							$transaction = $this->transactionBuilder->build($objectives);
							$transaction->addOptimizer(new \TYPO3\Zubrovka\Refactoring\TransactionOptimizer\NamespaceImportOptimizer);
							$this->stmts = $transaction->commit($this->stmts);
						} catch (\Exception $e) {
							$results[$filename] = $e;
						}
						file_put_contents($filename, $this->save());
					}
				}
				$iterator->next();
			}
		}
		return $results;
	}

}