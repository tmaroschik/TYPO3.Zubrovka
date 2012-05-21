<?php
namespace TYPO3\Zubrovka\Refactoring\Operation;

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
 * @FLOW3\Scope("prototype")
 */
class ChangeNamespaceName extends AbstractOperation {

	/**
	 * @param array $nodes
	 * @param \TYPO3\Zubrovka\Refactoring\OperationQueue $queue
	 * @return AbstractOperation
	 */
	public function prepare(array $nodes, \TYPO3\Zubrovka\Refactoring\OperationQueue $queue) {
		$usedBy = $this->node->getAttribute('usedBy');
		if ($usedBy !== NULL) {
			$usedBy = array_filter($usedBy, function($node) {
				if ($node instanceof \PHPParser_Node_Stmt_Class) {
					return false;
				} else {
					return true;
				}
			});
		}
	}

	/**
	 * @return void
	 */
	public function run() {
		$newNamespace = array_slice($this->newName->parts, 0, count($this->newName->parts) - 1);
		$this->node->set($newNamespace);
	}
}
