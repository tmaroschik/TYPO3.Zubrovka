<?php
namespace TYPO3\Zubrovka\Refactoring;

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
class Transaction {

	/**
	 * Contains operations
	 *
	 * @var \TYPO3\Zubrovka\Refactoring\Operation\OperationInterface[]
	 */
	protected $operations = array();

	/**
	 * Contains operationsByNodes
	 *
	 * @var array
	 */
	protected $operationsByNodes = array();

	/**
	 * Contains operationsByTypes
	 *
	 * @var array
	 */
	protected $operationsByTypes = array();

	/**
	 * @var TransactionOptimizer\PreCommitOptimizerInterface[]
	 */
	protected $preCommitOptimizers = array();

	/**
	 * @var TransactionOptimizer\PostCommitOptimizerInterface[]
	 */
	protected $postCommitOptimizers = array();

	/**
	 * @param \TYPO3\Zubrovka\Refactoring\Operation\OperationInterface $operations
	 */
	public function addOperations(array $operations) {
		foreach ($operations as $operation) {
			$this->addOperation($operation);
		}
	}

	/**
	 * @param \TYPO3\Zubrovka\Refactoring\Operation\OperationInterface $operation
	 */
	public function addOperation(\TYPO3\Zubrovka\Refactoring\Operation\OperationInterface $operation) {
		$nodeHash = spl_object_hash($operation->getNode());
		$operationHash = spl_object_hash($operation);
		$type = get_class($operation);
		$this->operations[$operationHash] = $operation;
		if (!isset($this->operationsByNodes[$nodeHash])) {
			$this->operationsByNodes[$nodeHash] = array();
		}
		$this->operationsByNodes[$nodeHash][$operationHash] = $operation;
		if (!isset($this->operationsByTypes[$type])) {
			$this->operationsByTypes[$type] = array();
		}
		$this->operationsByTypes[$type][$operationHash] = $operation;
	}

	public function removeOperation(\TYPO3\Zubrovka\Refactoring\Operation\OperationInterface $operation) {
		$nodeHash = spl_object_hash($operation->getNode());
		$operationHash = spl_object_hash($operation);
		$type = get_class($operation);
		$this->operations[$operationHash] = $operation;
		if (isset($this->operationsByNodes[$nodeHash][$operationHash])) {
			unset($this->operationsByNodes[$nodeHash][$operationHash]);
		}
		if (isset($this->operationsByNodes[$nodeHash][$operationHash])) {
			unset($this->operationsByNodes[$nodeHash][$operationHash]);
		}
		if (isset($this->operationsByTypes[$type][$operationHash])) {
			unset($this->operationsByTypes[$type][$operationHash]);
		}
	}

	/**
	 * @return Operation\OperationInterface[]
	 */
	public function getOperations() {
		return $this->operations;
	}

	/**
	 * @param string $type
	 * @return array
	 */
	public function getOperationsByType($type) {
		if (isset($this->operationsByTypes[$type])) {
			return $this->operationsByTypes[$type];
		}
		return array();
	}

	public function addOptimizer(TransactionOptimizer\TransactionOptimizerInterface $optimizer) {
		$objectHash = spl_object_hash($optimizer);
		if ($optimizer instanceof TransactionOptimizer\PreCommitOptimizerInterface) {
			$this->preCommitOptimizers[$objectHash] = $optimizer;
		}
		if ($optimizer instanceof TransactionOptimizer\PostCommitOptimizerInterface) {
			$this->postCommitOptimizers[$objectHash] = $optimizer;
		}
	}

	public function removeOptimizer(TransactionOptimizer\TransactionOptimizerInterface $optimizer) {
		$objectHash = spl_object_hash($optimizer);
		if (isset($this->preCommitOptimizers[$objectHash])) {
			unset($this->preCommitOptimizers[$objectHash]);
		}
		if (isset($this->postCommitOptimizers[$objectHash])) {
			unset($this->postCommitOptimizers[$objectHash]);
		}
	}

	/**
	 *
	 */
	public function commit() {
		foreach ($this->preCommitOptimizers as $optimizer) {
			$optimizer->optimize($this);
		}
		foreach ($this->operations as $operation) {
			if (!$operation->execute()) {
				// TODO throw exceptions
			}
		}
		foreach ($this->postCommitOptimizers as $optimizer) {
			$optimizer->optimize($this);
		}
	}


}
