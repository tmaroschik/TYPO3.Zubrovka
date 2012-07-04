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
class ChangeClassNameToNamespacedClassNameTask extends AbstractSubObjectiveTask {

	/**
	 * Contains a list of objective types that could be satisfied
	 *
	 * @var array
	 */
	protected $satisfiableObjectiveTypes = array(
		'TYPO3\Zubrovka\Refactoring\Objective\ChangeClassNameObjective{*}'
	);

	/**
	 * @param \TYPO3\Zubrovka\Refactoring\Objective\ObjectiveInterface[] $objectives
	 * @return int The score how good the the task can satisfy the objective
	 */
	public function canSatisfyObjectives() {
		$commonTargetNamespaces = $this->getObjectivesByTargetNamespace($this->objectives);
		if (NULL === $commonTargetNamespaces) {
			return 0;
		}
		foreach ($commonTargetNamespaces as $namespace => $changeClassNameObjectives) {
			$classNodes = array();
			foreach ($changeClassNameObjectives as $changeClassNameObjective) {
				$classNodes[] = $changeClassNameObjective->getNode();
				$this->operations[] =	$this->operationFactory->create(
					'\TYPO3\Zubrovka\Refactoring\Operation\ChangeClassNameOperation',
					$changeClassNameObjective->getNode(), $changeClassNameObjective->getNewName()->getLast()
				);
			}
			$this->subObjectives[] = new \TYPO3\Zubrovka\Refactoring\Objective\IntroduceNamespaceObjective(explode('\\', $namespace), $classNodes);
		}
		if (!empty($this->subObjectives)) {
			return -50;
		}
		return 0;
	}

	/**
	 * @return array
	 */
	protected function getObjectivesByTargetNamespace() {
		$objectivesByTargetNamespace = array();
		foreach ($this->objectives as $objective) {
			if ($objective instanceof \TYPO3\Zubrovka\Refactoring\Objective\ChangeClassNameObjective && $objective->getNewName()->isFullyQualified()) {
				$newName = implode('\\', array_slice($objective->getNewName()->getParts(), 0, -1));
				if (!isset($objectivesByTargetNamespace[$newName])) {
					$objectivesByTargetNamespace[$newName] = array();
				}
				$objectivesByTargetNamespace[$newName][] = $objective;
			} else {
				return NULL;
			}
		}
		return $objectivesByTargetNamespace;
	}

}