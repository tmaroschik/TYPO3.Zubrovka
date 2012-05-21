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
class ChangeName extends AbstractOperation {

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
				if ($alias->name->parts != array_slice($this->newName->parts, 0, count($alias->name->parts))) {
					// Leaves imported namespace
					$queuedUseStatementChanges = array_filter(iterator_to_array($queue), function($operation) use ($alias) {
						/** @var $operation OperationInterface */
						if ($operation instanceof ChangeUseStatement && $operation->getNode()->name->parts == $alias->name->parts) {
							return true;
						} else {
							return false;
						}
					});
					if (empty($queuedUseStatementChanges)) {
						$changeUseStatement = new ChangeUseStatement($alias, $this->newName);
						$queue->queue($changeUseStatement);
						$changeUseStatement->prepare($nodes, $queue);
					}
					$this->relativeNameParts = array_merge(array($alias->alias), array_slice($this->newName->parts, count($alias->name->parts)));
				} else {
					// Stays in imported namespace
					$this->relativeNameParts = array_slice($this->newName->parts, count($alias->name->parts) - 1);
					if ($alias->alias != $this->relativeNameParts[0]) {
						$this->relativeNameParts[0] = $alias->alias;
					}
					$namespacedName->set($this->newName);
				}
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
		} else {
			$this->relativeNameParts = $this->newName->parts;
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
