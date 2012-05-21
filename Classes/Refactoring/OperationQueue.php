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
class OperationQueue implements \Iterator, \Countable, \ArrayAccess {

	/**
	 * @var mixed
	 */
	protected $position;

	/**
	 * @var array
	 */
	protected $queue = array();

	/**
	 * @var array
	 */
	protected $types = array();

	/**
	 *
	 */
	public function __construct() {
		reset($this->queue);
	}

	/**
	 * @return int
	 */
	public function count() {
		return count($this->queue);
	}

	/**
	 *
	 */
	public function rewind() {
		reset($this->queue);
	}

	/**
	 * @return mixed
	 */
	public function current() {
		return current($this->queue);
	}

	/**
	 * @return mixed
	 */
	public function key() {
		return $this->position;
	}

	/**
	 *
	 */
	public function next() {
		next($this->queue);
	}

	/**
	 * @return bool
	 */
	public function valid() {
		return (bool)current($this->queue);
	}

	/**
	 * @param string $offset
	 * @param Operation\OperationInterface $operation
	 * @throws \InvalidArgumentException
	 */
	public function offsetSet($offset, $operation) {
		$offset = spl_object_hash($offset);
		$this->queue[$offset] = $operation;
		$this->types[get_class($operation)][$offset] &= $this->queue[$offset];
	}

	/**
	 * @param string $offset
	 * @return bool
	 */
	public function offsetExists($offset) {
		return isset($this->queue[$offset]);
	}

	/**
	 * @param string $offset
	 */
	public function offsetUnset($offset) {
		unset($this->queue[$offset]);
		foreach ($this->types as $operations) {
			if (isset($operations[$offset])) {
				unset($operations[$offset]);
				break;
			}
		}
	}

	/**
	 * @param string $offset
	 * @return null
	 */
	public function offsetGet($offset) {
		return isset($this->queue[$offset]) ? $this->queue[$offset] : null;
	}

	/**
	 * @param Operation\OperationInterface $operation
	 */
	public function queue(Operation\OperationInterface $operation) {
		$this->offsetSet(null, $operation);
	}

	/**
	 * @param Operation\OperationInterface $operation
	 */
	public function dequeue(Operation\OperationInterface $operation) {
		$this->offsetUnset(spl_object_hash($operation));
	}

	/**
	 * @param array $nodes
	 * @return \TYPO3\Zubrovka\Refactoring\OperationQueue
	 */
	public function run() {
		/** @var \TYPO3\Zubrovka\Refactoring\Operation\OperationInterface $operation */
		foreach ($this->queue as $operation) {
			$operation->run();
		}
		return $this;
	}

}
