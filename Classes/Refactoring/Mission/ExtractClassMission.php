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
class ExtractClassMission extends AbstractMission  {

	/**
	 * @var string
	 */
	protected $className;

	/**
	 * @var string
	 */
	protected $targetClassFile;

	/**
	 * @param string $oldName
	 * @param string $newName
	 */
	function __construct($className, $targetClassFile) {
		$this->className = $className;
		$this->targetClassFile = $targetClassFile;
		$this->analyzer = new \TYPO3\Zubrovka\Refactoring\Analysis\ExtractClassAnalyzer($className, $targetClassFile);
	}

	/**
	 * @return string
	 */
	public function getClassName() {
		return $this->className;
	}

	/**
	 * @return string
	 */
	public function getTargetClassFile() {
		return $this->targetClassFile;
	}


}