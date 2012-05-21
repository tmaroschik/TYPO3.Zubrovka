<?php
namespace TYPO3\Zubrovka\Refactoring\Operation;

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
abstract class AbstractOperation implements OperationInterface {

	/**
	 * @var \PHPParser_Node
	 */
	protected $node;

	/**
	 * @var \PHPParser_Node_Name
	 */
	protected $newName;

	/**
	 * @var int
	 */
	protected $state;

	/**
	 * @param \PHPParser_Node $
	 * @param \PHPParser_Node_Name $newName
	 */
	public function __construct(\PHPParser_Node $node, \PHPParser_Node_Name $newName) {
		$this->state = self::STATE_COMMISIONED;
		$this->node = $node;
		$this->newName = $newName;
	}

	/**
	 * @param array $nodes
	 * @param \TYPO3\Zubrovka\Refactoring\OperationQueue $queue
	 */
	public function prepare(array $nodes, \TYPO3\Zubrovka\Refactoring\OperationQueue $queue) {
		return $this;
	}

	/**
	 * @return \PHPParser_Node
	 */
	public function getNode() {
		return $this->node;
	}

	/**
	 * @param int $state
	 */
	public function setState($state) {
		$state = (int) $state;
		if (!in_array($state, array(self::STATE_COMMISIONED, self::STATE_PLANED))) {
			throw new \InvalidArgumentException('The given state "' . $state . '" is not a defined OperationInterface::STATE_* constant.', 1337596520);
		}
		$this->state = $state;
	}

	/**
	 * @return int
	 */
	public function getState() {
		return $this->state;
	}

}
