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
class RemoveNamespaceOperation extends AbstractOperation {

	/**
	 * @var \PHPParser_Node_Stmt_Class[]
	 */
	protected $classNodes;

	/**
	 * Contains the namespace
	 *
	 * @var \PHPParser_Node_Stmt_Namespace
	 */
	protected $namespace;

	/**
	 * @param \PHPParser_Node_Stmt_Namespace $namespace
	 * @param \PHPParser_Node_Stmt_Class[] $classNodes
	 */
	function __construct(\PHPParser_Node_Stmt_Namespace $namespace, array $classNodes = array()) {
		$this->namespace = $namespace;
		$this->classNodes = $classNodes;
		parent::__construct(current($classNodes));
	}


	/**
	 * @return bool
	 */
	public function execute(array &$stmts) {
		/** @var $firstClassNode \PHPParser_Node_Stmt_Class */
		$firstClassNode = current($this->classNodes);
		$classIgnorables = $this->namespace->getIgnorables() + $firstClassNode->getIgnorables();
		$firstClassNode->setIgnorables($classIgnorables);
		foreach ($stmts as $key => $stmt) {
			if ($stmt === $this->namespace) {
				array_splice($stmts, $key, 1, $this->classNodes);
				break;
			}
		}
		return true;
	}

}
