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
class ChangeNameOperation extends AbstractOperation {

	/**
	 * Contains the class name node
	 *
	 * @var \PHPParser_Node_Name
	 */
	protected $node;

	/**
	 * Contains the new relative name
	 *
	 * @var array
	 */
	protected $parts;

	/**
	 * @param \PHPParser_Node_Name $node
	 * @param array $parts
	 */
	function __construct(\PHPParser_Node_Name $node, array $parts) {
		$this->parts = $parts;
		parent::__construct($node);
	}

	/**
	 * @return bool
	 */
	public function execute(array &$stmts) {
		$this->node->setParts($this->parts);
		return true;
	}


}
