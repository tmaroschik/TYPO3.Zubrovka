<?php
namespace TYPO3\Zubrovka\Refactoring\Objective;

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
interface ObjectiveInterface {

	/**
	 * @abstract
	 * @return \PHPParser_Node
	 */
	public function getNode();

	/**
	 * @return \TYPO3\Zubrovka\Refactoring\Task\TaskInterface
	 */
	public function setParentTask(\TYPO3\Zubrovka\Refactoring\Task\TaskInterface $parentTask = NULL);

	/**
	 * @abstract
	 * @return \TYPO3\Zubrovka\Refactoring\Task\TaskInterface
	 */
	public function setTask(\TYPO3\Zubrovka\Refactoring\Task\TaskInterface  $task = NULL);

	/**
	 * @abstract
	 * @return bool
	 */
	public function isSatisfied();

	/**
	 * @abstract
	 * @return bool
	 */
	public function isSubObjective();

	/**
	 * @abstract
	 * @return \TYPO3\Zubrovka\Refactoring\Task\TaskInterface
	 */
	public function getTask();

}
