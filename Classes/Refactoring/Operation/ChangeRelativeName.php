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
class ChangeRelativeName extends AbstractOperation {

	/**
	 * @var array
	 */
	protected $relativeNameParts;

	/**
	 * @param array $nodes
	 * @param \TYPO3\Zubrovka\Refactoring\OperationQueue $queue
	 */
	public function prepare(array $nodes, \TYPO3\Zubrovka\Refactoring\OperationQueue $queue) {
		if (NULL !== $namespacedName = $this->node->getAttribute('namespacedName')) {

			if (NULL !== $alias = $this->node->getAttribute('alias')) {
			} elseif (NULL !== $namespace = $this->node->getAttribute('namespace')) {
				if ($namespace->parts != array_slice($this->newName->parts, 0, count($namespace->parts))) {
					// Leaves namespace
					//TODO implement addition of use statement
				} else {
					// Stays in namespace
					$this->relativeNameParts = array_slice($this->newName->parts, count($namespace->parts));
					$namespacedName->set($this->newName);
				}
			}
		}
	}

	/**
	 * @return void
	 */
	public function run() {
		if (!empty($this->relativeNameParts)) {
			$this->node->set($this->relativeNameParts);
		}
	}
}
