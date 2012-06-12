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
abstract class AbstractTask implements TaskInterface  {

	/**
	 * Contains operationFactory
	 *
	 * @var \TYPO3\Zubrovka\Refactoring\Operation\OperationFactory
	 */
	protected $operationFactory;

	/**
	 * Contains a list of objective types that could be satisfied
	 *
	 * @var array
	 */
	protected $satisfiableObjectiveTypes = array();

	/**
	 * Contains objectives
	 *
	 * @var \TYPO3\Zubrovka\Refactoring\Objective\ObjectiveInterface[]
	 */
	protected $objectives;

	/**
	 * Operations should be built during canSatisfyObjectives method to
	 * save cpu cycles
	 * @var \TYPO3\Zubrovka\Refactoring\Operation\OperationInterface[]
	 */
	protected $operations = array();

	/**
	 * Every task will create some operations and thus needs an operation factory.
	 *
	 * @param \TYPO3\Zubrovka\Refactoring\Operation\OperationFactory $operationFactory
	 */
	public function __construct(\TYPO3\Zubrovka\Refactoring\Operation\OperationFactory $operationFactory) {
		$this->operationFactory = $operationFactory;
	}

	/**
	 * @param array $objectives
	 * @return TaskInterface
	 */
	public function setObjectives(array $objectives) {
		$this->objectives = $objectives;
		$this->operations = array();
		return $this;
	}

	/**
	 * @return array
	 */
	public function getSatisfiableObjectiveTypes() {
		return $this->satisfiableObjectiveTypes;
	}


	/**
	 * @return \TYPO3\Zubrovka\Refactoring\Operation\OperationInterface[]
	 */
	public function getOperations() {
		return $this->operations;
	}
}