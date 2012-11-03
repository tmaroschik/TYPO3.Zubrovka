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
class ChangeClassNameInStringObjective extends AbstractObjective {

	/**
	 * @var string
	 */
	protected $oldName;

	/**
	 * @var string
	 */
	protected $newName;

	/**
	 * @param \PHPParser_Node_Scalar_String $node
	 * @param $oldName
	 * @param $newName
	 */
	public function __construct(\PHPParser_Node_Scalar_String $node, $oldName, $newName) {
		$this->oldName = (string) $oldName;
		$this->newName = (string) $newName;
		parent::__construct($node);
	}

	/**
	 * @return string
	 */
	public function getNewName() {
		return $this->newName;
	}

	/**
	 * @return string
	 */
	public function getOldName() {
		return $this->oldName;
	}


}
