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

interface SubObjectiveTaskInterface extends TaskInterface {

	/**
	 * @abstract
	 * @return int The score how good the the task can satisfy the objective. If returns smaller 0, subobjectives will be added to the list of objectives.
	 */
	public function canSatisfyObjectives();

	/**
	 * @abstract
	 * @return \TYPO3\Zubrovka\Refactoring\Objective\ObjectiveInterface[]
	 */
	public function getSubObjectives();

}
