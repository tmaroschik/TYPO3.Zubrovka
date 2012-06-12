<?php
namespace TYPO3\Zubrovka\Refactoring\Mission;

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
class RenameClassNameMission extends AbstractMission  {

	/**
	 * @var string
	 */
	protected $oldName;

	/**
	 * @var string
	 */
	protected $newName;

	/**
	 * @param string $oldName
	 * @param string $newName
	 */
	function __construct($oldName, $newName) {
		$this->oldName = $oldName;
		$this->newName = $newName;
		$this->analyzer = new \TYPO3\Zubrovka\Refactoring\Analysis\ChangeClassNameAnalyzer($oldName, $newName);
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