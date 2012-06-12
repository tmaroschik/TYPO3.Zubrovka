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
class ChangeNamespaceNameTask extends AbstractSubObjectiveTask {

	/**
	 * Contains a list of objective types that could be satisfied
	 *
	 * @var array
	 */
	protected $satisfiableObjectiveTypes = array(
		'TYPO3\Zubrovka\Refactoring\Objective\ChangeNamespaceNameObjective'
	);

	/**
	 * @param \TYPO3\Zubrovka\Refactoring\Objective\ObjectiveInterface[] $objectives
	 * @return int The score how good the the task can satisfy the objective
	 */
	public function canSatisfyObjectives() {
		/** @var $objective \TYPO3\Zubrovka\Refactoring\Objective\ChangeNamespaceNameObjective */
		$objective = current($this->objectives);
		if (FALSE === $objective && !$objective instanceof \TYPO3\Zubrovka\Refactoring\Objective\ChangeNamespaceNameObjective) {
			return 0;
		}
		/** @var $namespace \PHPParser_Node_Stmt_Namespace */
		$namespace = $objective->getNode();
		$newName = $objective->getNewName();
		if ($namespace !== NULL) {
			if (!$this->namespaceChangeHasSideEffects($namespace, $objective->getClassNodes())) {
				$this->operations = array(
					$this->operationFactory->create(
						'\TYPO3\Zubrovka\Refactoring\Operation\ChangeNameOperation',
						$namespace->getName(), $newName
					)
				);
				return 50;
			}
		}
		return 0;
	}

	/**
	 * @param \PHPParser_Node_Stmt_Namespace $namespace
	 * @param \PHPParser_Node[]
	 */
	protected function namespaceChangeHasSideEffects(\PHPParser_Node_Stmt_Namespace $namespace, array $ignorableNodes = array()) {
		$usedBy = $namespace->getAttribute('usedBy');
		if (NULL !== $usedBy) {
			/** @var $usedByNode \PHPParser_Node */
			foreach ($usedBy as $usedByNode) {
				/** @var $nodeNamespace \PHPParser_Node_Stmt_Namespace */
				$nodeNamespace = $usedByNode->getAttribute('namespace');
				if (!in_array($usedByNode, $ignorableNodes) && NULL !== $nodeNamespace && $nodeNamespace == $namespace) {
					return true;
				}
			}
		}
		return false;
	}


}