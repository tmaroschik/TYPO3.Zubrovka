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
class IntroduceNamespaceOperation extends AbstractOperation {

	/**
	 * @var \PHPParser_Node_Stmt_Class[]
	 */
	protected $classNodes;

	/**
	 * Contains the new namespace statement name
	 *
	 * @var array
	 */
	protected $name;

	/**
	 * @param array $name
	 * @param \PHPParser_Node_Stmt_Class[] $classNodes
	 */
	function __construct(array $name, array $classNodes = array()) {
		$this->name = $name;
		$this->classNodes = $classNodes;
		parent::__construct(current($classNodes));
	}


	/**
	 * @return bool
	 */
	public function execute(array &$stmts) {
		$namespaceIgnorables = array();
		$classIgnorables = array();
		$beforeDocComment = true;
		/** @var $firstClassNode \PHPParser_Node_Stmt_Class */
		$firstClassNode = current($this->classNodes);
		foreach ($firstClassNode->getIgnorables() as $ignorable) {
			if ($beforeDocComment && !$ignorable instanceof \PHPParser_Node_Ignorable_DocComment) {
				$namespaceIgnorables[] = $ignorable;
			} else {
				$classIgnorables[] = $ignorable;
				$beforeDocComment = false;
			}
		}
		$firstClassNode->setIgnorables($classIgnorables);
		foreach ($stmts as $key => $stmt) {
			if (in_array($stmt, $this->classNodes, true)) {
				unset($stmts[$key]);
			}
		}
		$stmts[] = new \PHPParser_Node_Stmt_Namespace(new \PHPParser_Node_Name($this->name), $this->classNodes, -1, $namespaceIgnorables);
		return true;
	}

}
