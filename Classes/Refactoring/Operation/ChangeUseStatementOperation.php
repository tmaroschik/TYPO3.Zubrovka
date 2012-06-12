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
class ChangeUseStatementOperation extends AbstractOperation {

	/**
	 * Contains the use statement node
	 *
	 * @var \PHPParser_Node_Stmt_UseUse
	 */
	protected $node;

	/**
	 * Contains the new use statement name
	 *
	 * @var array
	 */
	protected $name;

	/**
	 * Contains the new use statement alias
	 *
	 * @var string
	 */
	protected $alias;

	/**
	 * @param \PHPParser_Node_Stmt_UseUse $node
	 * @param array $name
	 * @param null|string $alias
	 */
	function __construct(\PHPParser_Node_Stmt_UseUse $node, array $name, $alias = null) {
		$this->name = $name;
		$this->alias = $alias;
		parent::__construct($node);
	}


	/**
	 * @return bool
	 */
	public function execute() {
		$this->node->getName()->set($this->name);
		$this->node->setAlias($this->alias);
		return true;
	}

}
