<?php
namespace TYPO3\Zubrovka\Refactoring\Objective;

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
class ChangeNamespaceNameObjective extends AbstractObjective {

	/**
	 * @var \PHPParser_Node[]
	 */
	protected $classNodes;

	/**
	 * @var array
	 */
	protected $newName;

	/**
	 * @param \PHPParser_Node_Stmt_Namespace $node
	 * @param array $newName
	 * @param \PHPParser_Node[] $classNodes
	 */
	public function __construct(\PHPParser_Node_Stmt_Namespace $node, array $newName, array $classNodes = array()) {
		$this->newName = $newName;
		$this->classNodes = $classNodes;
		parent::__construct($node);
	}

	/**
	 * @return array
	 */
	public function getNewName() {
		return $this->newName;
	}

	/**
	 * @return \PHPParser_Node[]
	 */
	public function getClassNodes() {
		return $this->classNodes;
	}

}
