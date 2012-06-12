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

/**
 *
 */
abstract class AbstractObjective implements ObjectiveInterface {

	/**
	 * @var \PHPParser_Node
	 */
	protected $node;

	/**
	 * @var \TYPO3\Zubrovka\Refactoring\Task\TaskInterface
	 */
	protected $parentTask;

	/**
	 * @var \TYPO3\Zubrovka\Refactoring\Task\TaskInterface
	 */
	protected $task;

	/**
	 * @param \PHPParser_Node $node
	 */
	public function __construct(\PHPParser_Node $node) {
		$this->node = $node;
	}

	/**
	 * @return \PHPParser_Node
	 */
	public function getNode() {
		return $this->node;
	}

	/**
	 * @param \TYPO3\Zubrovka\Refactoring\Task\TaskInterface $parentTask
	 * @return \TYPO3\Zubrovka\Refactoring\Task\TaskInterface
	 */
	public function setParentTask(\TYPO3\Zubrovka\Refactoring\Task\TaskInterface $parentTask = NULL) {
		$this->parentTask = $parentTask;
		return $this;
	}

	/**
	 * @param \TYPO3\Zubrovka\Refactoring\Task\TaskInterface $task
	 * @return \TYPO3\Zubrovka\Refactoring\Task\TaskInterface
	 */
	public function setTask(\TYPO3\Zubrovka\Refactoring\Task\TaskInterface $task = NULL) {
		$this->task = $task;
		return $this;
	}

	/**
	 * @return bool
	 */
	public function isSatisfied() {
		return isset($this->task);
	}

	/**
	 * @return bool
	 */
	public function isSubObjective() {
		return isset($this->parentTask);
	}

	/**
	 * @return \TYPO3\Zubrovka\Refactoring\Task\TaskInterface
	 */
	public function getTask() {
		return $this->task;
	}


}
