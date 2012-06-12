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
class ChangeRelativeNameObjective extends AbstractObjective {

	/**
	 * @var \PHPParser_Node_Name
	 */
	protected $newName;

	/**
	 * @param \PHPParser_Node $node
	 * @param \PHPParser_Node_Name $newName
	 */
	public function __construct(\PHPParser_Node_Name $node, \PHPParser_Node_Name $newName) {
		$this->newName = $newName;
		parent::__construct($node);
	}

	/**
	 * @return \PHPParser_Node_Name
	 */
	public function getNewName() {
		return $this->newName;
	}

}
