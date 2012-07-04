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
class ChangeNameTask extends AbstractSubObjectiveTask {

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
		/** @var $name \PHPParser_Node_Name */
		$name = $objective->getNode();
		$namespace = $name->getAttribute('namespace');
		$alias = $name->getAttribute('alias');
		$newName = $objective->getNewName();
		if (NULL === $namespace && $name->isUnqualified() && $newName->isUnqualified()) {
			$this->operations = array(
				$this->operationFactory->create(
					'\TYPO3\Zubrovka\Refactoring\Operation\ChangeNameOperation',
					$name, $newName->getParts()
				)
			);
			return 50;
		} elseif (NULL !== $namespace || NULL !== $alias) {
			// Names that use an imported namespace are resolved during runtime by the parser.
			// We assume here that if a name has a namespace or alias, it's a relative name.
			$this->subObjectives = array(
				new \TYPO3\Zubrovka\Refactoring\Objective\ChangeRelativeNameObjective($name, $newName)
			);
			return -50;
		}
		return 0;
	}


}