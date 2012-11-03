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
class ExtractClassObjective extends AbstractObjective {

	/**
	 * @var string
	 */
	protected $targetClassFile;

	/**
	 * @param \PHPParser_Node_Stmt $node
	 * @param $oldName
	 * @param $newName
	 */
	public function __construct(\PHPParser_Node_Stmt $node, $targetClassFile) {
		$this->targetClassFile = $targetClassFile;
		parent::__construct($node);
	}

	/**
	 * @return string
	 */
	public function getTargetClassFile() {
		return $this->targetClassFile;
	}

}
