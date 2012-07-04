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
class RemoveNamespaceTask extends AbstractTask {

	/**
	 * Contains a list of objective types that could be satisfied
	 *
	 * @var array
	 */
	protected $satisfiableObjectiveTypes = array(
		'TYPO3\Zubrovka\Refactoring\Objective\RemoveNamespaceObjective{*}'
	);

	/**
	 * @return int The score how good the the task can satisfy the objective
	 */
	public function canSatisfyObjectives() {
		$classNodesByNamespace = $this->getClassNodesByNamespace();
		if (NULL === $classNodesByNamespace) {
			return 0;
		}
		foreach ($classNodesByNamespace as $namespace => $classNodes) {
			$firstClassNode = current($classNodes);
			$namespace = $firstClassNode->getAttribute('namespace');
			$this->operations[] = $this->operationFactory->create(
				'TYPO3\Zubrovka\Refactoring\Operation\RemoveNamespaceOperation',
				$namespace, $classNodes
			);
		}
		if (!empty($this->operations)) {
			return 50;
		}
		return 0;
	}

	/**
	 * @param \TYPO3\Zubrovka\Refactoring\Objective\ObjectiveInterface[] $introduceNamespaceObjectives
	 * @return array
	 */
	protected function getClassNodesByNamespace() {
		$classNodesByNamespace = array();
		foreach ($this->objectives as $objective) {
			/** @var $objective \TYPO3\Zubrovka\Refactoring\Objective\RemoveNamespaceObjective */
			foreach ($objective->getClassNodes() as $classNode) {
				$namespace = $classNode->getAttribute('namespace');
				$namespaceName = (string) $namespace->getName();
				if (NULL === $namespace) {
					return NULL;
				}
				if (!isset($classNodesByNamespace[$namespaceName])) {
					$classNodesByNamespace[$namespaceName] = array();
				}
				$classNodesByNamespace[$namespaceName][] = $classNode;
			}
		}
		return $classNodesByNamespace;
	}

}