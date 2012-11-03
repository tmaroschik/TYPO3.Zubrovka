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
class ChangeClassNameOperation extends AbstractOperation {

	/**
	 * Contains the class name node
	 *
	 * @var \PHPParser_Node_Stmt
	 */
	protected $node;

	/**
	 * Contains the new fully qualified name name
	 *
	 * @var string
	 */
	protected $newName;

	/**
	 * @param \PHPParser_Node_Stmt $node
	 * @param string $newName
	 */
	function __construct(\PHPParser_Node_Stmt $node, $newName) {
		$this->newName = (string) $newName;
		parent::__construct($node);
	}

	/**
	 * @return bool
	 */
	public function execute(array &$stmts) {
		$this->node->setName($this->newName);
		return true;
	}


}
