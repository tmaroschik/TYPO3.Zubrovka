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

use TYPO3\FLOW3\Annotations as FLOW3;

/**
 * @FLOW3\Scope("prototype")
 */
interface OperationInterface {

	/**
	 * default state for unplaned operations
	 */
	const STATE_COMMISIONED = 0;

	/**
	 * state for planed operations
	 */
	const STATE_PLANED = 1;

	/**
	 * @abstract
	 * @param array $nodes
	 * @param \TYPO3\Zubrovka\Refactoring\OperationQueue $queue
	 * @return mixed
	 */
	public function prepare(array $nodes, \TYPO3\Zubrovka\Refactoring\OperationQueue $queue);

	/**
	 * @abstract
	 * @return void
	 */
	public function run();

	/**
	 * @abstract
	 * @return \PHPParser_Node
	 */
	public function getNode();

	/**
	 * @abstract
	 * @param int $state has to be one of defined STATE_* constants
	 * @return void
	 */
	public function setState($state);

	/**
	 * @abstract
	 * @return int
	 */
	public function getState();

}
