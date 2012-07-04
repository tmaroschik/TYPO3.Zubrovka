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
class ChangeFullyQualifiedNameOperation extends AbstractOperation {

	/**
	 * Contains the fully qualified name node
	 *
	 * @var \PHPParser_Node_Name_FullyQualified
	 */
	protected $node;

	/**
	 * Contains the new fully qualified name name
	 *
	 * @var array
	 */
	protected $name;

	/**
	 * @param \PHPParser_Node_Name_FullyQualified $node
	 * @param array $name
	 */
	function __construct(\PHPParser_Node_Name_FullyQualified $node, array $name) {
		$this->name = $name;
		parent::__construct($node);
	}

	/**
	 * @return bool
	 */
	public function execute(array &$stmts) {
		$this->node->set($this->name);
		return true;
	}


}
