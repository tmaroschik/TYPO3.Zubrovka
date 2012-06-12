<?php
namespace TYPO3\Zubrovka\Refactoring\Task;

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
class ChangeNamespacedNameChangeUseStatementTask extends AbstractTask {

	/**
	 * Contains a list of objective types that could be satisfied
	 *
	 * @var array
	 */
	protected $satisfiableObjectiveTypes = array(
		'TYPO3\Zubrovka\Refactoring\Objective\ChangeNameObjective'
	);

	/**
	 * @return int The score how good the the task can satisfy the objective
	 */
	public function canSatisfyObjectives() {
		/** @var $objective \TYPO3\Zubrovka\Refactoring\Objective\ChangeClassNameObjective */
		$objective = current($this->objectives);
		if (FALSE === $objective && !$objective instanceof \TYPO3\Zubrovka\Refactoring\Objective\ChangeNameObjective) {
			return 0;
		}
		$node = $objective->getNode();
		$namespacedName = $node->getAttribute('namespacedName');
		$alias = $node->getAttribute('alias');
		if (NULL !== $namespacedName && NULL !== $alias) {
			return 60;
		}
		return 0;
		// TODO Implment own task for every condition here and return different scores
//		if (NULL !== $namespacedName = $node->getAttribute('namespacedName')) {
//			if (NULL !== $alias = $node->getAttribute('alias')) {
//				if ($alias->getName()->getParts() != array_slice($this->newName->getParts(), 0, count($alias->getName()->getParts()))) {
//					// Leaves imported namespace
//					$queuedUseStatementChanges = array_filter(
//						$queue->getOperationByType('TYPO3\Zubrovka\Refactoring\Operation\ChangeUseStatementObjective'),
//						function(ChangeUseStatementObjective $operation) use ($alias) {
//						if ($operation->getNode()->getName()->getParts() == $alias->getName()->getParts()) {
//							return true;
//						} else {
//							return false;
//						}
//					});
//					if (empty($queuedUseStatementChanges)) {
//						$queue->queue(new ChangeUseStatementObjective($alias, $this->newName));
//					}
//					$this->relativeNameParts = array_merge(array($alias->getAlias()), array_slice($this->newName->getParts(), count($alias->getName()->getParts())));
//				} else {
//					// Stays in imported namespace
//					$this->relativeNameParts = array_slice($this->newName->getParts(), count($alias->getName()->getParts()) - 1);
//					if ($alias->getAlias() != $this->relativeNameParts[0]) {
//						$this->relativeNameParts[0] = $alias->getAlias();
//					}
//					$namespacedName->set($this->newName);
//				}
//			} elseif (NULL !== $namespace = $node->getAttribute('namespace')) {
//				if ($namespace->getParts() != array_slice($this->newName->getParts(), 0, count($namespace->getParts()))) {
//					// Leaves namespace
//					//TODO implement addition of use statement
//				} else {
//					// Stays in namespace
//					$this->relativeNameParts = array_slice($this->newName->getParts(), count($namespace->getParts()));
//					$namespacedName->set($this->newName);
//				}
//			}
//		} else {
//
//		}
	}

}