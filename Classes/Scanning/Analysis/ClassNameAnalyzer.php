<?php
namespace TYPO3\Zubrovka\Scanning\Analysis;

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
use TYPO3\Zubrovka\Refactoring;
use PHPParser_Exception_EscapeDeeperTraversalException;

/**
 * @FLOW3\Scope("prototype")
 */
class ClassNameAnalyzer extends \PHPParser_NodeVisitorAbstract implements \TYPO3\Zubrovka\Scanning\Analysis\AnalyzerInterface {

	/**
	 * @var null|\PHPParser_Node_Name Current namespace
	 */
	protected $namespace;

	/**
	 * @var array
	 */
	protected $results;

	/**
	 * @param array $nodes
	 */
	public function beforeTraverse(array $nodes) {
		$this->namespace = NULL;
		$this->results = array();
	}

	/**
	 * @param \PHPParser_Node $node
	 * @throws \PHPParser_Error
	 */
	public function enterNode(\PHPParser_Node $node) {
		switch ($node) {
			case $node instanceof \PHPParser_Node_Stmt_Namespace:
				/** @var $node \PHPParser_Node_Stmt_Namespace */
				$this->namespace = $node->getName();
				break;
			case $node instanceof \PHPParser_Node_Stmt_Class || $node instanceof \PHPParser_Node_Stmt_Interface:
				if (isset($this->namespace)) {
					$name = $this->namespace->getParts();
					$name[] = $node->getName();
					$this->results[] = '\\' . implode('\\', $name);
				} else {
					$this->results[] = $node->getName();
				}
				throw new PHPParser_Exception_EscapeDeeperTraversalException();
				break;
		}
	}

	/**
	 * @return array
	 */
	public function getResults() {
		return $this->results;
	}


}
