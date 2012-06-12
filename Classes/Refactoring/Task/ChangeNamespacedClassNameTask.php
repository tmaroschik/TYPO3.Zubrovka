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
class ChangeNamespacedClassNameTask extends AbstractSubObjectiveTask {

	/**
	 * Contains a list of objective types that could be satisfied
	 *
	 * @var array
	 */
	protected $satisfiableObjectiveTypes = array(
		'TYPO3\Zubrovka\Refactoring\Objective\ChangeClassNameObjective'
	);

	/**
	 * @param \TYPO3\Zubrovka\Refactoring\Objective\ObjectiveInterface[] $objectives
	 * @return int The score how good the the task can satisfy the objective
	 */
	public function canSatisfyObjectives() {
		/** @var $objective \TYPO3\Zubrovka\Refactoring\Objective\ChangeClassNameObjective */
		$objective = current($this->objectives);
		if (FALSE === $objective && !$objective instanceof \TYPO3\Zubrovka\Refactoring\Objective\ChangeClassNameObjective) {
			return 0;
		}
		$classNode = $objective->getNode();
		$newName = $objective->getNewName();
		/** @var $namespacedName \PHPParser_Node_Name_FullyQualified */
		$namespacedName = $classNode->getAttribute('namespacedName');
		$namespace = $classNode->getAttribute('namespace');
		if (NULL !== $namespacedName && NULL !== $namespace) {
			if ($namespacedName->getLast() != $newName->getLast()) {
				$this->operations = array(
					$this->operationFactory->create(
						'\TYPO3\Zubrovka\Refactoring\Operation\ChangeClassNameOperation',
						$classNode, $newName->getLast()
					)
				);
			}
			if (array_slice($namespacedName->getParts(), 0, -1) != array_slice($newName->getParts(), 0, -1)) {
				$this->subObjectives = array(
					new \TYPO3\Zubrovka\Refactoring\Objective\ChangeNamespaceNameObjective($namespace, array_slice($newName->getParts(), 0, -1), array($classNode))
				);
				return -50;
			}
			if (!empty($this->operations)) {
				return 50;
			}
		}
		return 0;
	}

}