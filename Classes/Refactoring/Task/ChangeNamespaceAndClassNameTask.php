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
class ChangeNamespaceAndClassNameTask extends AbstractTask {

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
		$classNameObjective = NULL;
		$namespaceObjective = NULL;
		foreach ($this->objectives as $objective) {
			switch ($objective) {
				case $objective instanceof \TYPO3\Zubrovka\Refactoring\Objective\ChangeClassNameObjective:
					$classNameObjective = $objective;
					break;
				case $objective instanceof \TYPO3\Zubrovka\Refactoring\Objective\ChangeNamespaceNameObjective:
					$namespaceObjective = $objective;
					break;
			}
		}
		if (NULL === $classNameObjective || NULL === $namespaceObjective) {
			return 0;
		}
		var_dump($classNameObjective, $namespaceObjective);
		die();
//		$classNode = $objective->getNode();
//		$newName = $objective->getNewName();
//		/** @var $namespacedName \PHPParser_Node_Name_FullyQualified */
//		$namespacedName = $classNode->getAttribute('namespacedName');
//		if (NULL === $namespacedName || (NULL !== $namespacedName && array_slice($namespacedName->getParts(), 0, -1) == array_slice($newName->getParts(), 0, -1))) {
//			$this->operations = array(
//				$this->operationFactory->create('\TYPO3\Zubrovka\Refactoring\Operation\ChangeClassNameOperation', $classNode, $newName->getLast())
//			);
//			return 50;
//		}
		return 0;
	}

}