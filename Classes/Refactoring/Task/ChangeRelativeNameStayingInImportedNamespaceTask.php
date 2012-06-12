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
class ChangeRelativeNameStayingInImportedNamespaceTask extends AbstractSubObjectiveTask {

	/**
	 * Contains a list of objective types that could be satisfied
	 *
	 * @var array
	 */
	protected $satisfiableObjectiveTypes = array(
		'TYPO3\Zubrovka\Refactoring\Objective\ChangeRelativeNameObjective'
	);

	/**
	 * @return int The score how good the the task can satisfy the objective
	 */
	public function canSatisfyObjectives() {
		/** @var $objective \TYPO3\Zubrovka\Refactoring\Objective\ChangeRelativeNameObjective */
		$objective = current($this->objectives);
		if (FALSE === $objective && !$objective instanceof \TYPO3\Zubrovka\Refactoring\Objective\ChangeRelativeNameObjective) {
			return 0;
		}
		/** @var $relativeName \PHPParser_Node_Name */
		$relativeName = $objective->getNode();
		/** @var $alias \PHPParser_Node_Stmt_UseUse */
		$alias = $relativeName->getAttribute('alias');
		$newName = $objective->getNewName();
		if (NULL !== $namespacedName = $relativeName->getAttribute('namespacedName')) {
			if (NULL !== $alias) {
				// Imported namespace
				$aliasName = $alias->getName();
				$otherImportedNamespaceName = array_slice($newName->getParts(), 0, count($aliasName->getParts()));
				if ($aliasName->getParts() == $otherImportedNamespaceName) {
					// Stays in namespace
					$relativeNameParts = array_slice($newName->getParts(), count($aliasName->getParts()) - 1);
					if ($alias->getAlias() != $relativeNameParts[0]) {
						$relativeNameParts[0] = $alias->getAlias();
					}
					$this->operations = array(
						$this->operationFactory->create(
							'\TYPO3\Zubrovka\Refactoring\Operation\ChangeNameOperation',
							$relativeName, $relativeNameParts
						)
					);
					return 50;
				}
			}
		}
		return 0;
	}

}
