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
class ChangeRelativeNameInDocCommentTask extends AbstractTask {

	/**
	 * Contains a list of objective types that could be satisfied
	 *
	 * @var array
	 */
	protected $satisfiableObjectiveTypes = array(
		'TYPO3\Zubrovka\Refactoring\Objective\ChangeRelativeNameInDocCommentObjective'
	);

	/**
	 * @return int The score how good the the task can satisfy the objective
	 */
	public function canSatisfyObjectives() {
		/** @var $objective \TYPO3\Zubrovka\Refactoring\Objective\ChangeRelativeNameInDocCommentObjective */
		$objective = current($this->objectives);
		if (FALSE === $objective && !$objective instanceof \TYPO3\Zubrovka\Refactoring\Objective\ChangeRelativeNameInDocCommentObjective) {
			return 0;
		}
		$alias = $objective->getAlias();
		$namespace = $objective->getNamespace();
		$newName = $objective->getNewName();
		if (NULL !== $alias) {
			if ($alias->getName()->getParts() != array_slice($newName->getParts(), 0, count($alias->getName()->getParts()))) {
				// Leaves imported namespace
				//TODO implement addition of use statement
				return 50;
			} else {
				// Stays in imported namespace
				$relativeNameParts = array_slice($newName->getParts(), count($alias->getName()->getParts()) - 1);
				if ($alias->getAlias() != $relativeNameParts[0]) {
					$relativeNameParts[0] = $alias->getAlias();
				}
				$this->operations = array(
					$this->operationFactory->create(
						'\TYPO3\Zubrovka\Refactoring\Operation\ChangeRelativeNameInDocCommentOperation',
						$objective->getNode(), $objective->getTagName(), $objective->getTagValue(), $relativeNameParts
					)
				);
				return 50;
			}
		} elseif (NULL !== $namespace) {
			$namespaceName = $namespace->getName();
			if ($namespaceName->getParts() != array_slice($newName->getParts(), 0, count($namespaceName->getParts()))) {
				// Leaves namespace
				//TODO implement addition of use statement
			} else {
				// Stays in namespace
				$relativeNameParts = array_slice($newName->getParts(), count($namespaceName->getParts()));
				$this->operations = array(
					$this->operationFactory->create(
						'\TYPO3\Zubrovka\Refactoring\Operation\ChangeRelativeNameInDocCommentOperation',
						$objective->getNode(), $objective->getTagName(), $objective->getTagValue(), $relativeNameParts
					)
				);
				return 50;
			}
		}
		return 0;
	}

}
